<?php
// app/Http/Controllers/Admin/TestModeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Room;
use App\Models\Player;
use App\Models\Progression;
use App\Models\GameEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TestModeController extends Controller
{
    /**
     * Interface du mode test
     */
    public function index()
    {
        $rooms = Room::orderBy('order')->get();
        $testTeam = Team::where('name', 'LIKE', 'TEST_%')->first();
        
        return view('admin.test-mode', compact('rooms', 'testTeam'));
    }
    
    /**
     * Créer une équipe de test
     */
    public function createTestTeam(Request $request)
    {
        // Supprimer l'ancienne équipe de test si elle existe
        Team::where('name', 'LIKE', 'TEST_%')->delete();
        
        // Créer une nouvelle équipe de test
        $team = Team::create([
            'name' => 'TEST_' . now()->format('His'),
            'code' => 'TEST' . rand(10, 99),
            'status' => 'playing',
            'started_at' => now(),
            'is_master' => true
        ]);
        
        // Créer un joueur admin
        $player = Player::create([
            'team_id' => $team->id,
            'name' => 'Admin Testeur',
            'device_id' => 'admin_test_' . Str::random(10),
            'is_active' => true,
            'last_activity' => now()
        ]);
        
        // Stocker en session pour le test
        session([
            'test_mode' => true,
            'test_team_id' => $team->id,
            'test_player_id' => $player->id
        ]);
        
        return response()->json([
            'success' => true,
            'team' => $team,
            'player' => $player
        ]);
    }
    
    /**
     * Simuler l'entrée dans une salle
     */
    public function enterRoom(Request $request, $roomId)
    {
        $room = Room::findOrFail($roomId);
        $teamId = session('test_team_id');
        
        if (!$teamId) {
            return response()->json(['error' => 'Aucune équipe de test active'], 400);
        }
        
        // Créer la progression
        $progression = Progression::updateOrCreate(
            [
                'team_id' => $teamId,
                'room_id' => $roomId
            ],
            [
                'status' => 'entered',
                'entered_at' => now()
            ]
        );
        
        // Logger l'événement
        GameEvent::create([
            'team_id' => $teamId,
            'room_id' => $roomId,
            'player_id' => session('test_player_id'),
            'event_type' => 'test_room_entered',
            'event_data' => ['test_mode' => true],
            'occurred_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'room' => $room,
            'progression' => $progression
        ]);
    }
    
    /**
     * Compléter instantanément une salle
     */
    public function completeRoom(Request $request, $roomId)
    {
        $room = Room::findOrFail($roomId);
        $teamId = session('test_team_id');
        
        $progression = Progression::where('team_id', $teamId)
            ->where('room_id', $roomId)
            ->first();
            
        if (!$progression) {
            return response()->json(['error' => 'Salle non commencée'], 400);
        }
        
        // Compléter instantanément
        $progression->update([
            'status' => 'completed',
            'completed_at' => now(),
            'time_spent' => rand(120, 600), // Temps aléatoire
            'digit_found' => $room->digit_reward ? true : false
        ]);
        
        return response()->json([
            'success' => true,
            'progression' => $progression,
            'digit' => $room->digit_reward
        ]);
    }
    
    /**
     * Réinitialiser le test
     */
    public function reset()
    {
        $teamId = session('test_team_id');
        
        if ($teamId) {
            // Supprimer toutes les données de test
            Team::where('id', $teamId)->delete();
        }
        
        session()->forget(['test_mode', 'test_team_id', 'test_player_id']);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Simuler un parcours complet
     */
    public function simulateFullRun(Request $request)
    {
        $speed = $request->input('speed', 'normal'); // fast, normal, slow
        $teamId = session('test_team_id');
        
        if (!$teamId) {
            return response()->json(['error' => 'Créez d\'abord une équipe de test'], 400);
        }
        
        $rooms = Room::where('type', 'main')->orderBy('order')->get();
        $results = [];
        
        foreach ($rooms as $index => $room) {
            // Temps de base selon la vitesse
            $baseTime = [
                'fast' => rand(60, 180),
                'normal' => rand(180, 420),
                'slow' => rand(420, 900)
            ][$speed];
            
            // Créer la progression
            $progression = Progression::create([
                'team_id' => $teamId,
                'room_id' => $room->id,
                'status' => 'completed',
                'entered_at' => now()->subSeconds($baseTime + 60),
                'completed_at' => now()->subSeconds(60),
                'time_spent' => $baseTime,
                'digit_found' => $room->digit_reward ? true : false,
                'penalties' => rand(0, 2)
            ]);
            
            $results[] = [
                'room' => $room->name,
                'time' => gmdate('i:s', $baseTime),
                'digit' => $room->digit_reward
            ];
        }
        
        // Mettre à jour l'équipe
        $team = Team::find($teamId);
        $totalTime = array_sum(array_column($results, 'time'));
        $team->update([
            'status' => 'finished',
            'finished_at' => now(),
            'total_time' => $totalTime
        ]);
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'total_time' => gmdate('H:i:s', $totalTime)
        ]);
    }
    
    /**
     * Tester un mini-jeu spécifique
     */
    public function testMinigame($roomId)
    {
        $room = Room::findOrFail($roomId);
        
        // Créer une session de test temporaire
        session(['testing_room_' . $roomId => true]);
        
        // Rediriger vers la vue du mini-jeu
        return redirect()->route('game.room', ['slug' => $room->slug])
            ->with('test_mode', true);
    }
    
    /**
     * Vérifier la connectivité
     */
    public function checkConnectivity()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'websockets' => $this->checkWebSockets(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage()
        ];
        
        return response()->json($checks);
    }
    
    private function checkDatabase()
    {
        try {
            \DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Base de données connectée'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkWebSockets()
    {
        try {
            // Vérifier si le serveur WebSocket répond
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');
            
            $fp = @fsockopen($host, $port, $errno, $errstr, 5);
            if ($fp) {
                fclose($fp);
                return ['status' => 'ok', 'message' => 'WebSockets actif'];
            } else {
                return ['status' => 'error', 'message' => 'WebSockets inaccessible'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkCache()
    {
        try {
            cache()->put('test_key', 'test_value', 1);
            $value = cache()->get('test_key');
            cache()->forget('test_key');
            
            return ['status' => 'ok', 'message' => 'Cache fonctionnel'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkStorage()
    {
        try {
            $writable = is_writable(storage_path());
            return [
                'status' => $writable ? 'ok' : 'error',
                'message' => $writable ? 'Storage accessible' : 'Storage non accessible en écriture'
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
<?php
// app/Http/Controllers/GameController.php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Progression;
use App\Models\Team;
use App\Models\Player;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Page principale du jeu
     */
    public function index(Request $request)
    {
        $player = $this->getPlayer($request);
        $team = $player->team;
        
        // Récupérer la progression actuelle
        $currentProgression = $team->progressions()
            ->with('room')
            ->whereIn('status', ['entered', 'in_progress'])
            ->latest()
            ->first();
            
        // Récupérer les chiffres trouvés
        $foundDigits = $team->progressions()
            ->join('rooms', 'progressions.room_id', '=', 'rooms.id')
            ->where('progressions.digit_found', true)
            ->whereNotNull('rooms.digit_reward')
            ->pluck('rooms.digit_reward')
            ->toArray();
        
        return view('game.index', [
            'player' => $player,
            'team' => $team,
            'currentRoom' => $currentProgression?->room,
            'foundDigits' => $foundDigits,
            'gameStarted' => $team->status === 'playing'
        ]);
    }
    
    /**
     * Page du scanner QR
     */
    public function scanner(Request $request)
    {
        $player = $this->getPlayer($request);
        $team = $player->team;
        
        if ($team->status !== 'playing') {
            return redirect()->route('game.index')
                ->with('error', 'Le jeu n\'a pas encore commencé');
        }
        
        return view('game.scanner');
    }
    
    /**
     * Page d'une salle spécifique
     */
    public function room(Request $request, $slug)
    {
        $player = $this->getPlayer($request);
        $team = $player->team;
        
        // Vérifier que l'équipe a accès à cette salle
        $room = Room::where('slug', $slug)->firstOrFail();
        
        $progression = Progression::where('team_id', $team->id)
            ->where('room_id', $room->id)
            ->first();
            
        if (!$progression) {
            return redirect()->route('game.index')
                ->with('error', 'Vous n\'avez pas accès à cette salle');
        }
        
        // Charger la vue spécifique à la salle
        $viewName = 'game.rooms.' . str_replace('-', '_', $slug);
        
        if (!view()->exists($viewName)) {
            // Vue générique si pas de vue spécifique
            $viewName = 'game.rooms.generic';
        }
        
        return view($viewName, [
            'player' => $player,
            'team' => $team,
            'room' => $room,
            'progression' => $progression
        ]);
    }
    
    /**
     * Récupérer le joueur depuis la session
     */
    private function getPlayer(Request $request)
    {
        $playerId = session('player_id');
        $deviceId = session('device_id');
        
        if (!$playerId || !$deviceId) {
            abort(401, 'Non authentifié');
        }
        
        return Player::where('id', $playerId)
            ->where('device_id', $deviceId)
            ->with('team')
            ->firstOrFail();
    }
}
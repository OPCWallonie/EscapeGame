<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Traiter la création d'équipe depuis le formulaire web
     */
    public function createTeam(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string|max:255',
            'player_name' => 'required|string|max:255',
            'is_master' => 'boolean'
        ]);

        // Récupérer ou générer le device_id
        $deviceId = session('device_id');
        if (!$deviceId) {
            $deviceId = 'web_' . Str::random(16);
            session(['device_id' => $deviceId]);
        }

        // Vérifier si ce device a déjà un joueur
        $existingPlayer = Player::where('device_id', $deviceId)->first();
        if ($existingPlayer) {
            return redirect()->route('game.index')
                ->with('info', 'Vous êtes déjà dans une équipe');
        }

        // Générer un code unique
        do {
            $code = strtoupper(Str::random(6));
        } while (Team::where('code', $code)->exists());

        // Créer l'équipe
        $team = Team::create([
            'name' => $request->team_name,
            'code' => $code,
            'status' => 'waiting',
            'is_master' => $request->boolean('is_master', false)
        ]);

        // Créer le joueur
        $player = Player::create([
            'team_id' => $team->id,
            'name' => $request->player_name,
            'device_id' => $deviceId,
            'is_active' => true,
            'last_activity' => now()
        ]);

        // Stocker en session
        session([
            'player_id' => $player->id,
            'team_id' => $team->id
        ]);

        return redirect()->route('team.show')
            ->with('success', "Équipe créée ! Code : {$team->code}");
    }

    /**
     * Traiter la connexion à une équipe
     */
    public function joinTeam(Request $request)
    {
        $request->validate([
            'team_code' => 'required|string|size:6',
            'player_name' => 'required|string|max:255'
        ]);

        // Récupérer ou générer le device_id
        $deviceId = session('device_id');
        if (!$deviceId) {
            $deviceId = 'web_' . Str::random(16);
            session(['device_id' => $deviceId]);
        }

        // Vérifier si ce device a déjà un joueur
        $existingPlayer = Player::where('device_id', $deviceId)->first();
        if ($existingPlayer) {
            return redirect()->route('game.index')
                ->with('info', 'Vous êtes déjà dans une équipe');
        }

        // Trouver l'équipe
        $team = Team::where('code', strtoupper($request->team_code))->first();

        if (!$team) {
            return back()
                ->withInput()
                ->with('error', 'Code d\'équipe invalide');
        }

        // Vérifications
        if ($team->status !== 'waiting') {
            return back()
                ->withInput()
                ->with('error', 'Cette équipe a déjà commencé le jeu');
        }

        if ($team->players()->count() >= 10) {
            return back()
                ->withInput()
                ->with('error', 'L\'équipe est complète (10 joueurs maximum)');
        }

        // Créer le joueur
        $player = Player::create([
            'team_id' => $team->id,
            'name' => $request->player_name,
            'device_id' => $deviceId,
            'is_active' => true,
            'last_activity' => now()
        ]);

        // Stocker en session
        session([
            'player_id' => $player->id,
            'team_id' => $team->id
        ]);

        return redirect()->route('game.index')
            ->with('success', 'Vous avez rejoint l\'équipe !');
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        session()->forget(['player_id', 'team_id', 'device_id']);
        return redirect()->route('home')
            ->with('info', 'Vous avez été déconnecté');
    }
}

// app/Http/Controllers/TeamController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Afficher les détails de l'équipe
     */
    public function show(Request $request)
    {
        $player = $this->getPlayer($request);
        $team = $player->team;
        
        $team->load(['players' => function($query) {
            $query->where('is_active', true)
                  ->orderBy('created_at');
        }]);
        
        return view('team.show', compact('player', 'team'));
    }
    
    /**
     * Afficher la progression de l'équipe
     */
    public function progress(Request $request)
    {
        $player = $this->getPlayer($request);
        $team = $player->team;
        
        $progressions = $team->progressions()
            ->with('room')
            ->orderBy('created_at')
            ->get();
            
        $completedRooms = $progressions->where('status', 'completed');
        $totalTime = $completedRooms->sum('time_spent');
        
        $foundDigits = $progressions
            ->where('digit_found', true)
            ->map(fn($p) => $p->room->digit_reward)
            ->filter()
            ->values();
        
        return view('team.progress', compact(
            'player', 
            'team', 
            'progressions', 
            'completedRooms',
            'totalTime',
            'foundDigits'
        ));
    }
    
    /**
     * Récupérer le joueur depuis la session
     */
    private function getPlayer(Request $request)
    {
        $playerId = session('player_id');
        $deviceId = session('device_id');
        
        return Player::where('id', $playerId)
            ->where('device_id', $deviceId)
            ->with('team')
            ->firstOrFail();
    }
}
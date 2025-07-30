<?php
// app/Http/Controllers/Api/TeamController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * Créer une nouvelle équipe
     */
    public function create(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string|max:255',
            'player_name' => 'required|string|max:255',
            'device_id' => 'required|string|unique:players,device_id',
            'is_master' => 'boolean'
        ]);

        // Générer un code unique pour l'équipe
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

        // Créer le premier joueur
        $player = Player::create([
            'team_id' => $team->id,
            'name' => $request->player_name,
            'device_id' => $request->device_id,
            'is_active' => true,
            'last_activity' => now()
        ]);

        return response()->json([
            'success' => true,
            'team_id' => $team->id,
            'team_code' => $team->code,
            'player_id' => $player->id,
            'message' => 'Équipe créée avec succès !'
        ], 201);
    }

    /**
     * Rejoindre une équipe existante
     */
    public function join(Request $request)
    {
        $request->validate([
            'team_code' => 'required|string|size:6',
            'player_name' => 'required|string|max:255',
            'device_id' => 'required|string|unique:players,device_id'
        ]);

        // Trouver l'équipe
        $team = Team::where('code', strtoupper($request->team_code))->first();

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Code d\'équipe invalide'
            ], 404);
        }

        // Vérifier que l'équipe n'a pas déjà commencé
        if ($team->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'Cette équipe a déjà commencé le jeu'
            ], 403);
        }

        // Vérifier le nombre de joueurs (max 10)
        if ($team->players()->count() >= 10) {
            return response()->json([
                'success' => false,
                'message' => 'L\'équipe est complète (10 joueurs maximum)'
            ], 403);
        }

        // Créer le joueur
        $player = Player::create([
            'team_id' => $team->id,
            'name' => $request->player_name,
            'device_id' => $request->device_id,
            'is_active' => true,
            'last_activity' => now()
        ]);

        return response()->json([
            'success' => true,
            'team_id' => $team->id,
            'player_id' => $player->id,
            'message' => 'Vous avez rejoint l\'équipe avec succès !'
        ]);
    }

    /**
     * Obtenir les informations de l'équipe
     */
    public function info($teamId)
    {
        $team = Team::with(['players' => function($query) {
            $query->where('is_active', true);
        }])->findOrFail($teamId);

        return response()->json([
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'code' => $team->code,
                'status' => $team->status,
                'player_count' => $team->players->count(),
                'is_master' => $team->is_master,
                'players' => $team->players->map(function($player) {
                    return [
                        'id' => $player->id,
                        'name' => $player->name,
                        'role' => $player->role
                    ];
                })
            ]
        ]);
    }

    /**
     * Démarrer le jeu pour l'équipe
     */
    public function start($teamId)
    {
        $team = Team::findOrFail($teamId);

        if ($team->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'Le jeu a déjà commencé'
            ], 400);
        }

        // Vérifier qu'il y a au moins 2 joueurs
        if ($team->players()->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Il faut au moins 2 joueurs pour commencer'
            ], 400);
        }

        $team->update([
            'status' => 'playing',
            'started_at' => now()
        ]);

        // Diffuser l'événement à tous les joueurs de l'équipe
        broadcast(new \App\Events\GameStateUpdated(
            $team->id,
            'game',
            'game_started',
            ['message' => 'Le jeu commence !']
        ));

        return response()->json([
            'success' => true,
            'message' => 'Le jeu a commencé !'
        ]);
    }
}
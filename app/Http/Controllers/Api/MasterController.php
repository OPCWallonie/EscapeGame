<?php
// app/Http/Controllers/Api/MasterController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Room;
use App\Models\Progression;
use App\Events\GameStateUpdated;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    /**
     * Vue d'ensemble pour le maître du jeu
     */
    public function overview(Request $request)
    {
        $teams = Team::with(['players' => function($query) {
            $query->where('is_active', true);
        }, 'progressions.room'])
        ->where('status', '!=', 'finished')
        ->get();
        
        $overview = $teams->map(function($team) {
            $currentRoom = $team->progressions()
                ->whereIn('status', ['entered', 'in_progress'])
                ->with('room')
                ->latest()
                ->first();
                
            $completedRooms = $team->progressions()
                ->where('status', 'completed')
                ->count();
                
            $foundDigits = $team->progressions()
                ->join('rooms', 'progressions.room_id', '=', 'rooms.id')
                ->where('progressions.digit_found', true)
                ->whereNotNull('rooms.digit_reward')
                ->pluck('rooms.digit_reward')
                ->toArray();
                
            $totalTime = $team->started_at 
                ? $team->started_at->diffInSeconds(now()) 
                : 0;
            
            return [
                'id' => $team->id,
                'name' => $team->name,
                'code' => $team->code,
                'status' => $team->status,
                'player_count' => $team->players->count(),
                'current_room' => $currentRoom ? [
                    'id' => $currentRoom->room->id,
                    'name' => $currentRoom->room->name,
                    'entered_at' => $currentRoom->entered_at,
                    'time_in_room' => $currentRoom->entered_at->diffInSeconds(now())
                ] : null,
                'progress' => [
                    'completed_rooms' => $completedRooms,
                    'found_digits' => $foundDigits,
                    'total_time' => $totalTime,
                    'penalties' => $team->penalties
                ]
            ];
        });
        
        return response()->json([
            'teams' => $overview,
            'total_teams' => $teams->count(),
            'playing_teams' => $teams->where('status', 'playing')->count()
        ]);
    }
    
    /**
     * Jouer un son pour toutes les équipes
     */
    public function playSound(Request $request, $sound)
    {
        $validSounds = [
            'game_start' => 'Début du jeu',
            'room_complete' => 'Salle complétée',
            'digit_found' => 'Chiffre trouvé',
            'hint_available' => 'Indice disponible',
            'time_warning' => 'Attention au temps',
            'finale' => 'Finale'
        ];
        
        if (!array_key_exists($sound, $validSounds)) {
            return response()->json([
                'success' => false,
                'message' => 'Son invalide'
            ], 400);
        }
        
        // Diffuser à toutes les équipes
        broadcast(new GameStateUpdated(
            0, // 0 = toutes les équipes
            'master',
            'play_sound',
            [
                'sound' => $sound,
                'description' => $validSounds[$sound]
            ]
        ))->toOthers();
        
        return response()->json([
            'success' => true,
            'message' => "Son '$validSounds[$sound]' envoyé à toutes les équipes"
        ]);
    }
    
    /**
     * Envoyer un indice à une équipe
     */
    public function sendHint(Request $request, $teamId)
    {
        $request->validate([
            'hint' => 'required|string|max:500',
            'room_id' => 'nullable|exists:rooms,id'
        ]);
        
        $team = Team::findOrFail($teamId);
        $hint = $request->input('hint');
        $roomId = $request->input('room_id');
        
        // Diffuser l'indice
        broadcast(new GameStateUpdated(
            $team->id,
            'master',
            'hint_received',
            [
                'hint' => $hint,
                'room_id' => $roomId,
                'timestamp' => now()->toTimeString()
            ]
        ));
        
        // Ajouter une pénalité pour l'indice
        $team->increment('penalties');
        
        return response()->json([
            'success' => true,
            'message' => 'Indice envoyé',
            'team' => $team->name,
            'new_penalty_count' => $team->penalties
        ]);
    }
    
    /**
     * Obtenir toutes les équipes avec détails
     */
    public function getAllTeams(Request $request)
    {
        $teams = Team::with([
            'players',
            'progressions' => function($query) {
                $query->with('room')->orderBy('created_at');
            },
            'gameEvents' => function($query) {
                $query->latest()->limit(10);
            }
        ])->get();
        
        return response()->json([
            'teams' => $teams->map(function($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'code' => $team->code,
                    'status' => $team->status,
                    'is_master' => $team->is_master,
                    'started_at' => $team->started_at,
                    'finished_at' => $team->finished_at,
                    'total_time' => $team->total_time,
                    'penalties' => $team->penalties,
                    'players' => $team->players->map(function($player) {
                        return [
                            'id' => $player->id,
                            'name' => $player->name,
                            'role' => $player->role,
                            'is_active' => $player->is_active,
                            'last_activity' => $player->last_activity
                        ];
                    }),
                    'progression_timeline' => $team->progressions->map(function($prog) {
                        return [
                            'room' => $prog->room->name,
                            'status' => $prog->status,
                            'entered_at' => $prog->entered_at,
                            'completed_at' => $prog->completed_at,
                            'time_spent' => $prog->time_spent,
                            'digit_found' => $prog->digit_found
                        ];
                    }),
                    'recent_events' => $team->gameEvents->map(function($event) {
                        return [
                            'type' => $event->event_type,
                            'data' => $event->event_data,
                            'occurred_at' => $event->occurred_at
                        ];
                    })
                ];
            })
        ]);
    }
    
    /**
     * Forcer le démarrage du jeu pour toutes les équipes
     */
    public function startAllGames(Request $request)
    {
        $teams = Team::where('status', 'waiting')->get();
        
        if ($teams->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune équipe en attente'
            ]);
        }
        
        foreach ($teams as $team) {
            $team->update([
                'status' => 'playing',
                'started_at' => now()
            ]);
            
            broadcast(new GameStateUpdated(
                $team->id,
                'master',
                'game_started',
                ['message' => 'Le maître du jeu a lancé la partie !']
            ));
        }
        
        // Son global de démarrage
        broadcast(new GameStateUpdated(
            0,
            'master',
            'play_sound',
            ['sound' => 'game_start']
        ));
        
        return response()->json([
            'success' => true,
            'message' => "{$teams->count()} équipes ont démarré",
            'teams_started' => $teams->pluck('name')
        ]);
    }
    
    /**
     * Terminer le jeu pour une équipe
     */
    public function finishTeam(Request $request, $teamId)
    {
        $team = Team::findOrFail($teamId);
        
        if ($team->status === 'finished') {
            return response()->json([
                'success' => false,
                'message' => 'Cette équipe a déjà terminé'
            ]);
        }
        
        $totalTime = $team->started_at 
            ? $team->started_at->diffInSeconds(now()) 
            : 0;
            
        $team->update([
            'status' => 'finished',
            'finished_at' => now(),
            'total_time' => $totalTime
        ]);
        
        broadcast(new GameStateUpdated(
            $team->id,
            'master',
            'game_finished',
            [
                'message' => 'Félicitations ! Vous avez terminé !',
                'total_time' => $totalTime,
                'penalties' => $team->penalties
            ]
        ));
        
        return response()->json([
            'success' => true,
            'message' => "L'équipe {$team->name} a terminé",
            'total_time' => gmdate('H:i:s', $totalTime),
            'penalties' => $team->penalties
        ]);
    }
}
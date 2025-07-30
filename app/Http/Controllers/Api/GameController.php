<?php
// app/Http/Controllers/Api/GameController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Progression;
use App\Models\GameEvent;
use App\Events\GameStateUpdated;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GameController extends Controller
{
    /**
     * Obtenir la salle actuelle de l'équipe
     */
    public function currentRoom(Request $request)
    {
        $team = $request->get('team');
        
        $currentProgression = $team->progressions()
            ->with('room')
            ->whereIn('status', ['entered', 'in_progress'])
            ->latest()
            ->first();
            
        if (!$currentProgression) {
            return response()->json([
                'current_room' => null,
                'message' => 'Aucune salle active. Scannez un QR code pour commencer.'
            ]);
        }
        
        return response()->json([
            'current_room' => [
                'id' => $currentProgression->room->id,
                'name' => $currentProgression->room->name,
                'slug' => $currentProgression->room->slug,
                'description' => $currentProgression->room->description,
                'mini_game' => $currentProgression->room->mini_game_config,
                'status' => $currentProgression->status,
                'entered_at' => $currentProgression->entered_at,
                'time_spent' => $currentProgression->entered_at->diffInSeconds(now())
            ]
        ]);
    }
    
    /**
     * Compléter un défi/mini-jeu
     */
    public function completeChallenge(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'success' => 'required|boolean',
            'data' => 'array'
        ]);
        
        $player = $request->get('player');
        $team = $request->get('team');
        $roomId = $request->input('room_id');
        
        // Vérifier la progression
        $progression = Progression::where('team_id', $team->id)
            ->where('room_id', $roomId)
            ->whereIn('status', ['entered', 'in_progress'])
            ->first();
            
        if (!$progression) {
            return response()->json([
                'success' => false,
                'message' => 'Progression non trouvée pour cette salle'
            ], 404);
        }
        
        $room = $progression->room;
        
        if ($request->input('success')) {
            // Marquer comme complété
            $timeSpent = $progression->entered_at->diffInSeconds(now());
            
            $progression->update([
                'status' => 'completed',
                'completed_at' => now(),
                'time_spent' => $timeSpent,
                'game_data' => array_merge(
                    $progression->game_data ?? [],
                    $request->input('data', [])
                )
            ]);
            
            // Si la salle donne un chiffre
            if ($room->digit_reward) {
                $progression->update(['digit_found' => true]);
                
                // Diffuser l'événement
                broadcast(new GameStateUpdated(
                    $team->id,
                    $room->slug,
                    'digit_found',
                    [
                        'digit' => $room->digit_reward,
                        'room_name' => $room->name,
                        'player_name' => $player->name
                    ]
                ));
            }
            
            // Logger l'événement
            GameEvent::create([
                'team_id' => $team->id,
                'room_id' => $roomId,
                'player_id' => $player->id,
                'event_type' => 'challenge_completed',
                'event_data' => [
                    'time_spent' => $timeSpent,
                    'data' => $request->input('data')
                ],
                'occurred_at' => now()
            ]);
            
            // Diffuser la complétion
            broadcast(new GameStateUpdated(
                $team->id,
                $room->slug,
                'room_completed',
                [
                    'room_name' => $room->name,
                    'time_spent' => $timeSpent,
                    'next_room_order' => $room->order + 1
                ]
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Défi complété !',
                'digit_found' => $room->digit_reward,
                'time_spent' => $timeSpent
            ]);
            
        } else {
            // Échec du défi
            $progression->update([
                'status' => 'in_progress',
                'penalties' => $progression->penalties + 1
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Défi échoué. Réessayez !',
                'penalties' => $progression->penalties
            ]);
        }
    }
    
    /**
     * Action spécifique dans une salle
     */
    public function roomAction(Request $request, $roomId)
    {
        $request->validate([
            'action' => 'required|string',
            'data' => 'array'
        ]);
        
        $player = $request->get('player');
        $team = $request->get('team');
        
        $room = Room::findOrFail($roomId);
        $progression = Progression::where('team_id', $team->id)
            ->where('room_id', $roomId)
            ->first();
            
        if (!$progression) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas accès à cette salle'
            ], 403);
        }
        
        // Logger l'action
        GameEvent::create([
            'team_id' => $team->id,
            'room_id' => $roomId,
            'player_id' => $player->id,
            'event_type' => 'room_action',
            'event_data' => [
                'action' => $request->input('action'),
                'data' => $request->input('data')
            ],
            'occurred_at' => now()
        ]);
        
        // Traiter l'action selon la salle
        $response = $this->processRoomAction(
            $room,
            $progression,
            $request->input('action'),
            $request->input('data', [])
        );
        
        // Diffuser si nécessaire
        if ($response['broadcast'] ?? false) {
            broadcast(new GameStateUpdated(
                $team->id,
                $room->slug,
                $response['broadcast_action'],
                $response['broadcast_data']
            ));
        }
        
        return response()->json($response);
    }
    
    /**
     * Traiter une action spécifique selon la salle
     */
    private function processRoomAction($room, $progression, $action, $data)
    {
        switch ($room->slug) {
            case 'onze-caves':
                return $this->processCavesAction($progression, $action, $data);
                
            case 'centre-controle':
                return $this->processControlCenterAction($progression, $action, $data);
                
            case 'bureaux-pluie':
                return $this->processRainOfficeAction($progression, $action, $data);
                
            default:
                return [
                    'success' => true,
                    'message' => 'Action enregistrée'
                ];
        }
    }
    
    /**
     * Actions pour les 11 caves
     */
    private function processCavesAction($progression, $action, $data)
    {
        if ($action === 'collect_fragment') {
            $caveNumber = $data['cave_number'] ?? 0;
            $gameData = $progression->game_data ?? [];
            $collectedFragments = $gameData['fragments'] ?? [];
            
            if (in_array($caveNumber, $collectedFragments)) {
                return [
                    'success' => false,
                    'message' => 'Fragment déjà collecté'
                ];
            }
            
            $collectedFragments[] = $caveNumber;
            $gameData['fragments'] = $collectedFragments;
            
            $progression->update(['game_data' => $gameData]);
            
            // Vérifier si tous les vrais fragments sont collectés (caves 1-9)
            $realFragments = array_filter($collectedFragments, fn($f) => $f <= 9);
            $allCollected = count($realFragments) == 9;
            
            return [
                'success' => true,
                'message' => 'Fragment collecté',
                'total_fragments' => count($collectedFragments),
                'is_fake' => $caveNumber > 9,
                'all_collected' => $allCollected,
                'broadcast' => true,
                'broadcast_action' => 'fragment_collected',
                'broadcast_data' => [
                    'cave_number' => $caveNumber,
                    'total' => count($collectedFragments)
                ]
            ];
        }
        
        return ['success' => false, 'message' => 'Action inconnue'];
    }
    
    /**
     * Actions pour le centre de contrôle
     */
    private function processControlCenterAction($progression, $action, $data)
    {
        if ($action === 'submit_pattern') {
            $pattern = $data['pattern'] ?? [];
            $correctPattern = ['red', 'blue', 'blue', 'green', 'red']; // À randomiser en production
            
            $gameData = $progression->game_data ?? [];
            $attempts = $gameData['pattern_attempts'] ?? 0;
            
            if ($pattern === $correctPattern) {
                return [
                    'success' => true,
                    'message' => 'Pattern correct !',
                    'digit_revealed' => true
                ];
            } else {
                $gameData['pattern_attempts'] = $attempts + 1;
                $progression->update(['game_data' => $gameData]);
                
                if ($attempts >= 2) {
                    // Pénalité après 3 essais
                    $progression->increment('penalties');
                }
                
                return [
                    'success' => false,
                    'message' => 'Pattern incorrect',
                    'attempts_left' => 3 - ($attempts + 1)
                ];
            }
        }
        
        return ['success' => false, 'message' => 'Action inconnue'];
    }
    
    /**
     * Actions pour les bureaux sous la pluie
     */
    private function processRainOfficeAction($progression, $action, $data)
    {
        if ($action === 'check_stillness') {
            $stillnessDuration = $data['duration'] ?? 0;
            
            if ($stillnessDuration >= 10) {
                return [
                    'success' => true,
                    'message' => 'Le chiffre apparaît dans la pluie...',
                    'digit_revealed' => true,
                    'broadcast' => true,
                    'broadcast_action' => 'stillness_achieved',
                    'broadcast_data' => ['duration' => $stillnessDuration]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Restez immobile plus longtemps',
                'required_duration' => 10,
                'current_duration' => $stillnessDuration
            ];
        }
        
        return ['success' => false, 'message' => 'Action inconnue'];
    }
    
    /**
     * Obtenir la progression globale
     */
    public function progress(Request $request)
    {
        $team = $request->get('team');
        
        $progressions = $team->progressions()
            ->with('room')
            ->orderBy('created_at')
            ->get();
            
        $completedRooms = $progressions->where('status', 'completed')->count();
        $totalMainRooms = Room::where('type', 'main')->count();
        
        $foundDigits = $progressions
            ->where('digit_found', true)
            ->map(fn($p) => $p->room->digit_reward)
            ->filter()
            ->values();
            
        $totalTime = $progressions
            ->where('status', 'completed')
            ->sum('time_spent');
            
        $totalPenalties = $progressions->sum('penalties');
        
        return response()->json([
            'progress' => [
                'completed_rooms' => $completedRooms,
                'total_rooms' => $totalMainRooms,
                'percentage' => round(($completedRooms / $totalMainRooms) * 100),
                'found_digits' => $foundDigits,
                'total_time_seconds' => $totalTime,
                'total_penalties' => $totalPenalties,
                'current_room' => $progressions->whereIn('status', ['entered', 'in_progress'])->first()?->room
            ],
            'timeline' => $progressions->map(function($p) {
                return [
                    'room_name' => $p->room->name,
                    'status' => $p->status,
                    'entered_at' => $p->entered_at,
                    'completed_at' => $p->completed_at,
                    'time_spent' => $p->time_spent,
                    'digit_found' => $p->digit_found
                ];
            })
        ]);
    }
    
    /**
     * Logger un événement générique
     */
    public function logEvent(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'event_data' => 'array'
        ]);
        
        $player = $request->get('player');
        $team = $request->get('team');
        
        GameEvent::create([
            'team_id' => $team->id,
            'player_id' => $player->id,
            'event_type' => $request->input('event_type'),
            'event_data' => $request->input('event_data'),
            'occurred_at' => now()
        ]);
        
        return response()->json(['success' => true]);
    }
}
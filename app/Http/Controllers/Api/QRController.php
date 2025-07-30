<?php
// app/Http/Controllers/Api/QRController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Progression;
use App\Models\GameEvent;
use App\Events\GameStateUpdated;
use Illuminate\Http\Request;

class QRController extends Controller
{
    /**
     * Scanner un QR code
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $player = $request->get('player');
        $team = $request->get('team');
        $qrCode = $request->input('qr_code');

        // Trouver la salle correspondante
        $room = Room::where('qr_code', $qrCode)->first();

        if (!$room) {
            // Logger l'échec
            GameEvent::create([
                'team_id' => $team->id,
                'player_id' => $player->id,
                'event_type' => 'invalid_qr_scan',
                'event_data' => ['qr_code' => $qrCode],
                'occurred_at' => now()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'QR Code invalide'
            ], 404);
        }

        // Vérifier que le jeu a commencé
        if ($team->status !== 'playing') {
            return response()->json([
                'success' => false,
                'message' => 'Le jeu n\'a pas encore commencé'
            ], 403);
        }

        // Vérifier la progression
        $canAccess = $this->checkRoomAccess($team, $room);
        
        if (!$canAccess['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $canAccess['message']
            ], 403);
        }

        // Créer ou mettre à jour la progression
        $progression = Progression::updateOrCreate(
            [
                'team_id' => $team->id,
                'room_id' => $room->id
            ],
            [
                'status' => 'entered',
                'entered_at' => now()
            ]
        );

        // Logger l'événement
        GameEvent::create([
            'team_id' => $team->id,
            'room_id' => $room->id,
            'player_id' => $player->id,
            'event_type' => 'room_entered',
            'event_data' => [
                'qr_code' => $qrCode,
                'room_name' => $room->name
            ],
            'occurred_at' => now()
        ]);

        // Diffuser l'événement à l'équipe
        broadcast(new GameStateUpdated(
            $team->id,
            $room->slug,
            'room_entered',
            [
                'room_id' => $room->id,
                'room_name' => $room->name,
                'player_name' => $player->name,
                'mini_game' => $room->mini_game_config
            ]
        ));

        return response()->json([
            'success' => true,
            'message' => "Bienvenue dans : {$room->name}",
            'room_id' => $room->id,
            'room_name' => $room->name,
            'room_slug' => $room->slug,
            'mini_game' => $room->mini_game_config,
            'redirect' => "/game/room/{$room->slug}"
        ]);
    }

    /**
     * Vérifier l'accès à une salle
     */
    private function checkRoomAccess($team, $room)
    {
        // Première salle toujours accessible
        if ($room->order == 1) {
            return ['allowed' => true];
        }

        // Pour les embranchements, vérifier que la salle parent est complétée
        if ($room->type === 'branch' && $room->parent_room_id) {
            $parentProgression = Progression::where('team_id', $team->id)
                ->where('room_id', $room->parent_room_id)
                ->where('status', 'completed')
                ->first();

            if (!$parentProgression) {
                return [
                    'allowed' => false,
                    'message' => 'Vous devez d\'abord terminer la salle précédente'
                ];
            }

            // Vérifier le nombre de joueurs pour "Chez Guy"
            if ($room->slug === 'chez-guy') {
                $playersInRoom = GameEvent::where('team_id', $team->id)
                    ->where('room_id', $room->id)
                    ->where('event_type', 'room_entered')
                    ->distinct('player_id')
                    ->count('player_id');

                if ($playersInRoom >= 3) {
                    return [
                        'allowed' => false,
                        'message' => 'Cette salle est limitée à 3 explorateurs'
                    ];
                }
            }

            return ['allowed' => true];
        }

        // Pour les salles principales, vérifier que la précédente est complétée
        $previousRoom = Room::where('type', 'main')
            ->where('order', '<', $room->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previousRoom) {
            $previousProgression = Progression::where('team_id', $team->id)
                ->where('room_id', $previousRoom->id)
                ->where('status', 'completed')
                ->first();

            if (!$previousProgression) {
                return [
                    'allowed' => false,
                    'message' => "Vous devez d'abord terminer : {$previousRoom->name}"
                ];
            }
        }

        return ['allowed' => true];
    }
}
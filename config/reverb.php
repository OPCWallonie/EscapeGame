<?php

// config/reverb.php
return [
    'default' => 'reverb',
    'apps' => [
        [
            'id' => env('REVERB_APP_ID'),
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'options' => [
                'host' => env('REVERB_HOST', '0.0.0.0'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
            ],
        ],
    ],
];

// app/Events/GameEvents/PlayerJoinedRoom.php
namespace App\Events\GameEvents;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerJoinedRoom implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $gameId,
        public string $roomId,
        public array $player
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel("game.{$this->gameId}.room.{$this->roomId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'player.joined';
    }
}

// app/Events/GameEvents/RoomProgressUpdated.php
namespace App\Events\GameEvents;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $gameId,
        public string $roomId,
        public array $progress,
        public ?string $clueDigit = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("game.{$this->gameId}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'progress' => $this->progress,
            'clue_digit' => $this->clueDigit,
        ];
    }
}

// app/Http/Controllers/GameController.php
namespace App\Http\Controllers;

use App\Events\GameEvents\PlayerJoinedRoom;
use App\Events\GameEvents\RoomProgressUpdated;
use App\Models\Game;
use App\Models\Room;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function joinRoom(Request $request, Game $game, Room $room)
    {
        $player = auth()->user();
        
        // Logique pour rejoindre la salle
        $game->players()->attach($player->id, [
            'current_room_id' => $room->id,
            'joined_at' => now(),
        ]);

        // Broadcast l'événement
        broadcast(new PlayerJoinedRoom(
            $game->id,
            $room->id,
            [
                'id' => $player->id,
                'name' => $player->name,
                'avatar' => $player->avatar_url,
            ]
        ))->toOthers();

        return response()->json([
            'success' => true,
            'room' => $room->only(['id', 'name', 'challenge_type']),
        ]);
    }

    public function completeChallenge(Request $request, Game $game, Room $room)
    {
        $validated = $request->validate([
            'challenge_data' => 'required|array',
        ]);

        // Vérifier la solution du challenge
        $isCorrect = $this->verifyChallengeResponse($room, $validated['challenge_data']);

        if ($isCorrect) {
            // Mettre à jour la progression
            $progress = $game->updateProgress($room->id);
            
            // Récupérer le chiffre de la clé si applicable
            $clueDigit = $room->clue_digit;

            // Broadcast la progression
            broadcast(new RoomProgressUpdated(
                $game->id,
                $room->id,
                $progress,
                $clueDigit
            ));

            return response()->json([
                'success' => true,
                'clue_digit' => $clueDigit,
                'next_room_hint' => $room->next_room_hint,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Solution incorrecte',
        ], 422);
    }

    private function verifyChallengeResponse(Room $room, array $data): bool
    {
        return match($room->challenge_type) {
            'ar_key_door' => $this->verifyARKeyDoor($data),
            'qr_scan' => $this->verifyQRCode($data),
            'mini_game' => $this->verifyMiniGame($data),
            'ar_object' => $this->verifyARObject($data),
            default => false,
        };
    }
}

// app/Http/Controllers/ARController.php
namespace App\Http\Controllers;

use App\Models\ARMarker;
use Illuminate\Http\Request;

class ARController extends Controller
{
    public function validateARTarget(Request $request)
    {
        $validated = $request->validate([
            'target_id' => 'required|string',
            'position' => 'required|array',
            'room_id' => 'required|exists:rooms,id',
        ]);

        $marker = ARMarker::where('identifier', $validated['target_id'])
            ->where('room_id', $validated['room_id'])
            ->first();

        if (!$marker) {
            return response()->json(['valid' => false]);
        }

        // Vérifier la position si nécessaire
        $isValid = $this->validatePosition($marker, $validated['position']);

        return response()->json([
            'valid' => $isValid,
            'marker_type' => $marker->type,
            'interaction_data' => $marker->interaction_data,
        ]);
    }
}

// routes/channels.php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('game.{gameId}', function ($user, $gameId) {
    return $user->games()->where('games.id', $gameId)->exists();
});

Broadcast::channel('game.{gameId}.room.{roomId}', function ($user, $gameId, $roomId) {
    if ($user->games()->where('games.id', $gameId)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar_url,
        ];
    }
});

// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// resources/js/game/GameSync.js
class GameSync {
    constructor(gameId) {
        this.gameId = gameId;
        this.currentRoom = null;
        this.players = new Map();
        this.initializeChannels();
    }

    initializeChannels() {
        // Canal principal du jeu
        this.gameChannel = window.Echo.channel(`game.${this.gameId}`);
        
        // Écouter les mises à jour de progression
        this.gameChannel.listen('.room.progress', (e) => {
            this.handleProgressUpdate(e);
        });
    }

    joinRoom(roomId) {
        // Quitter l'ancienne salle
        if (this.currentRoom) {
            window.Echo.leave(`game.${this.gameId}.room.${this.currentRoom}`);
        }

        // Rejoindre la nouvelle salle
        this.currentRoom = roomId;
        this.roomChannel = window.Echo.join(`game.${this.gameId}.room.${roomId}`)
            .here((users) => {
                // Joueurs déjà présents
                users.forEach(user => this.players.set(user.id, user));
                this.updatePlayersList();
            })
            .joining((user) => {
                // Nouveau joueur
                this.players.set(user.id, user);
                this.showNotification(`${user.name} a rejoint la salle`);
                this.updatePlayersList();
            })
            .leaving((user) => {
                // Joueur qui part
                this.players.delete(user.id);
                this.updatePlayersList();
            });
    }

    handleProgressUpdate(data) {
        // Mettre à jour l'interface
        if (data.clue_digit) {
            this.revealClueDigit(data.clue_digit);
        }
        
        // Afficher l'indice pour la prochaine salle
        if (data.next_room_hint) {
            this.showNextRoomHint(data.next_room_hint);
        }
    }

    updatePlayersList() {
        // Mettre à jour l'affichage des joueurs connectés
        const playersList = document.getElementById('players-list');
        playersList.innerHTML = Array.from(this.players.values())
            .map(player => `
                <div class="player-card">
                    <img src="${player.avatar}" alt="${player.name}">
                    <span>${player.name}</span>
                </div>
            `).join('');
    }
}
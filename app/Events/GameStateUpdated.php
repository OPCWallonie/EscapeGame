<?php
// app/Events/GameStateUpdated.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStateUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teamId;
    public $currentRoom;
    public $action;
    public $data;

    public function __construct($teamId, $currentRoom, $action, $data = [])
    {
        $this->teamId = $teamId;
        $this->currentRoom = $currentRoom;
        $this->action = $action;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new Channel('team.' . $this->teamId);
    }

    public function broadcastAs()
    {
        return 'game.state.updated';
    }

    public function broadcastWith()
    {
        return [
            'team_id' => $this->teamId,
            'current_room' => $this->currentRoom,
            'action' => $this->action,
            'data' => $this->data,
            'timestamp' => now()->timestamp,
        ];
    }
}
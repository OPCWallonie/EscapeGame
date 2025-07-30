<?php
// app/Http/Controllers/WebSocketTestController.php

namespace App\Http\Controllers;

use App\Events\GameStateUpdated;
use Illuminate\Http\Request;

class WebSocketTestController extends Controller
{
    public function testBroadcast(Request $request)
    {
        $teamId = $request->input('team_id', 1);
        $room = $request->input('room', 'galerie');
        $action = $request->input('action', 'test');
        
        event(new GameStateUpdated($teamId, $room, $action, [
            'message' => 'Test de diffusion WebSocket',
            'test' => true
        ]));

        return response()->json([
            'status' => 'broadcast sent',
            'team_id' => $teamId,
            'room' => $room,
            'action' => $action
        ]);
    }
}

// Ajouter dans routes/web.php ou routes/api.php :
// Route::post('/test-websocket', [WebSocketTestController::class, 'testBroadcast']);
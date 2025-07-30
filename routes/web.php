<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\QRController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques (sans authentification)
Route::post('/create-team', [TeamController::class, 'create']);
Route::post('/join-team', [TeamController::class, 'join']);

// Routes qui nécessitent d'être dans une équipe
Route::middleware(['auth.player'])->group(function () {
    // Équipe
    Route::get('/team/{teamId}', [TeamController::class, 'info']);
    Route::post('/team/{teamId}/start', [TeamController::class, 'start']);
    
    // Jeu
    Route::get('/game/current-room', [GameController::class, 'currentRoom']);
    Route::post('/game/scan-qr', [QRController::class, 'scan']);
    Route::post('/game/complete-challenge', [GameController::class, 'completeChallenge']);
    Route::get('/game/progress', [GameController::class, 'progress']);
    
    // Mini-jeux spécifiques
    Route::post('/game/rooms/{roomId}/action', [GameController::class, 'roomAction']);
    
    // Événements temps réel
    Route::post('/game/event', [GameController::class, 'logEvent']);
});

// Routes pour le maître du jeu
Route::middleware(['auth.master'])->prefix('master')->group(function () {
    Route::get('/overview', [MasterController::class, 'overview']);
    Route::post('/sound/{sound}', [MasterController::class, 'playSound']);
    Route::post('/hint/{teamId}', [MasterController::class, 'sendHint']);
    Route::get('/teams', [MasterController::class, 'getAllTeams']);
});

//Test WebSocket
Route::post('/test-websocket', [WebSocketTestController::class, 'testBroadcast']);
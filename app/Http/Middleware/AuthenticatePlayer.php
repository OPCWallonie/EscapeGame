<?php
// app/Http/Middleware/AuthenticatePlayer.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Player;

class AuthenticatePlayer
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $playerId = $request->header('X-Player-ID') ?? $request->input('player_id');
        $deviceId = $request->header('X-Device-ID') ?? $request->input('device_id');

        if (!$playerId || !$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        $player = Player::where('id', $playerId)
            ->where('device_id', $deviceId)
            ->where('is_active', true)
            ->first();

        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Joueur non trouvé ou inactif'
            ], 401);
        }

        // Mettre à jour la dernière activité
        $player->update(['last_activity' => now()]);

        // Attacher le joueur à la requête
        $request->merge(['player' => $player]);
        $request->merge(['team' => $player->team]);

        return $next($request);
    }
}

// app/Http/Middleware/AuthenticateMaster.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateMaster
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $player = $request->get('player');
        $team = $request->get('team');

        if (!$team || !$team->is_master) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé au téléphone maître'
            ], 403);
        }

        return $next($request);
    }
}

// N'oubliez pas d'enregistrer ces middlewares dans app/Http/Kernel.php :
// protected $routeMiddleware = [
//     // ...
//     'auth.player' => \App\Http\Middleware\AuthenticatePlayer::class,
//     'auth.master' => \App\Http\Middleware\AuthenticateMaster::class,
// ];
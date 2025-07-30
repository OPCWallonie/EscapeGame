<?php
// app/Http/Middleware/AuthenticatePlayerWeb.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Player;

class AuthenticatePlayerWeb
{
    /**
     * Handle an incoming request for web routes.
     */
    public function handle(Request $request, Closure $next)
    {
        $playerId = session('player_id');
        $deviceId = session('device_id');

        if (!$playerId || !$deviceId) {
            return redirect()->route('home')
                ->with('error', 'Vous devez rejoindre une équipe pour accéder au jeu');
        }

        $player = Player::where('id', $playerId)
            ->where('device_id', $deviceId)
            ->where('is_active', true)
            ->first();

        if (!$player) {
            session()->forget(['player_id', 'device_id', 'team_id']);
            return redirect()->route('home')
                ->with('error', 'Session expirée. Veuillez vous reconnecter');
        }

        // Mettre à jour la dernière activité
        $player->update(['last_activity' => now()]);

        // Rendre le joueur disponible dans les vues
        view()->share('currentPlayer', $player);
        view()->share('currentTeam', $player->team);

        return $next($request);
    }
}

// app/Http/Middleware/AuthenticateAdmin.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateAdmin
{
    /**
     * Handle an incoming request for admin routes.
     * Simple protection - à améliorer en production
     */
    public function handle(Request $request, Closure $next)
    {
        // Option 1 : Protection par mot de passe dans l'URL
        if ($request->input('admin_key') !== env('ADMIN_KEY', 'escape2024')) {
            
            // Option 2 : Protection par IP
            $allowedIps = explode(',', env('ADMIN_IPS', '127.0.0.1'));
            if (!in_array($request->ip(), $allowedIps)) {
                abort(403, 'Accès non autorisé');
            }
        }

        return $next($request);
    }
}

// N'oubliez pas d'ajouter dans .env :
// ADMIN_KEY=escape2024
// ADMIN_IPS=127.0.0.1,192.168.1.100
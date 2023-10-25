<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RemoveUserPusher
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {

            $userId = Auth::id(); // Récupérer l'ID de l'utilisateur connecté
            dd($userId);
            // Faites quelque chose avec $userId avant que la session ne soit supprimée.
        }

        return $next($request);
    }
}

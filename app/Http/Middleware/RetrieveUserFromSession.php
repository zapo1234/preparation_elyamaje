<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RetrieveUserFromSession {

    public function handle($request, Closure $next) {

        if ($user = $request->session()->get('user')) {
            Auth::setUser($user);
        } else {
            $user = Auth::user();
            $request->session()->put('user', $user);
        }

        return $next($request);
    }
}
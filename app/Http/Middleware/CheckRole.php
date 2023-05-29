<?php

namespace App\Http\Middleware;
use Closure;

class CheckRole
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth()->user();
        
        if(in_array($user->role_id,$roles)) {
            return $next($request);
        }

        abort(403, 'Accès refusé');
    }

}
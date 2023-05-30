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
        auth()->user()->hasAnyRole($roles);
        // Auth()->user()->roles->toArray()[0]['id']
        if(!auth()->user()->hasAnyRole($roles)){
            abort(403);
        }
        return $next($request);
        // $user = Auth()->user();
        // $roleIds = $user->roles()
        //     ->wherePivot('user_roles.user_id', $user->id)
        //     ->pluck('user_roles.role_id')
        //     ->toArray();

        // auth()->user()->roles = $roleIds;

    
        // if(count(array_intersect($roleIds, $roles)) > 0){
            // return $next($request);
        // }

        // abort(403, 'Accès refusé');
    }

}
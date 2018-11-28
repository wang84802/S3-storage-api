<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

class IsUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $api = $request->header('Api-Token');
        if($api==NULL)
            return response('Unauthenticated.', 401);
        $users = User::where('api_token','=',$api)->get();
        if($users=='[]') {
            return response('Unauthenticated.', 401);
        }else{
            return $next($request);
        }
    }
}

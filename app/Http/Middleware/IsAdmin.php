<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

class IsAdmin
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

        $users = User::where('api_token','=',$api)->get();
        if($users=='[]') {
            return response('Unauthenticated.', 401);
        } else if ($users[0]->type != 'admin'){
            return response('Unauthorized.', 403);
        }   else{
            return $next($request);
        }

    }

}

<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\ParameterBag;
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
        $api = $request->input('api_token');

        $users = User::where('api_token','=',$api)->get();
        if($users == '[]') {
            return response('Unauthorized.', 401);
        } else if ($users[0]->type != 'admin'){
            return response('Not admin user.', 403);
        }   else{
            return $next($request);
        }

    }

}

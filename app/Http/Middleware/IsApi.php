<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use DB;
use App\Repositories\UserRepository;

class IsApi
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

        $result = $this->Token_Exist($api);
        if(!$result)
        {
            return response()->json(
            [
                'status' => 401,
                'error' => [
                    'message' => 'Unauthorized.'
                ],
            ]
            ,401);
        }

        else
            return $next($request);
    }
    public function Token_Exist($api)
    {
        return DB::table('api_token')->where('token',$api)->exists();
    }
}

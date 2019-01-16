<?php

namespace App\Http\Middleware;

use Log;
use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;


class JwtMiddleware extends BaseMiddleware
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
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {

            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => 401,'error' => ['message' => 'Token is invalid.']],400);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' => 401,'error' => ['message' => 'Token is expired.']],400);
            }else{
                return response()->json(['status' => 404,'error' => ['message' => 'Authorization token not found.']],400);
            }
        }
        return $next($request);
    }
}

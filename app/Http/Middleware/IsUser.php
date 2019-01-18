<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use App\Repositories\UserRepository;

class IsUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    private $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function handle($request, Closure $next)
    {
        $api = $request->header('Api-Token');

        $api_token = '123';
        if($api !== $api_token)
            return response('Unauthenticated.', 401);
        else
            return $next($request);

    }
}

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
        $status = $this->userRepository->getStatusByToken($api);
        if($api==NULL)
            return response('Unauthenticated.', 401);
        $users = User::where('api_token','=',$api)->get();
        if($users=='[]') {
            return response('Unauthenticated.', 401);
        }
        if($status=='logout')
            return response('User is not login.',401);
        else{
            return $next($request);
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepository;
use App\Notifications\UserNotification;
use Notification;
use Log;

class LoginController extends Controller
{
    public function test(Request $request)
    {
        return $request;
    }
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    protected $userRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->UserRepository = $userRepository;
        $this->middleware('guest')->except('logout');
    }
    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user();
            //$user->generateToken();
            $user->Status_Login();
            $message = 'User login.';
            Notification::route('slack', 'https://hooks.slack.com/services/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')->notify(new UserNotification($user,$message));
            Log::info($user->toArray());
            return response()->json([
                'data' => $user->toArray(),
            ]);
        }

        return $this->sendFailedLoginResponse($request);
    }
    public function logout(Request $request)
    {
        $api = $request->header('Api-Token');
        $user = User::where('api_token','=',$api)->get();

        //$user = Auth::guard('api')->user();
        if($user=='[]')
            return response()->json(['response' => 'User does not exist!'], 404);
        else
            $user = $user[0];
        if($user) {
            $user->status = 'logout';
            //$user->api_token = null;
            $user->save();
            $message = 'User logout.';
            Notification::route('slack', env('SLACK_WEBHOOK2'))->notify(new UserNotification($user,$message));

            return response()->json(['response' => $user->name.' logged out.'], 200);
        }else {
            return response()->json(['response' => 'User does not exist!'], 404);
        }

    }
}

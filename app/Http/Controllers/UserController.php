<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        $array = array();
        //return $credentials = $request->only('email','password');
        try {
            if (! $token = JWTAuth::attempt($request->all())) {
                return response()->json(
                    ['status' => 401,'error' =>
                        [
                            'message' => 'invalid_credentials'
                        ]
                    ], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $array['status'] = 200;
        $array['data']['token'] = $token;
        return response()->json($array);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirm' => 'required_with:password|same:password|min:6'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        $array['status'] = 201;
        $array['data']['user'] = $user;
        $array['data']['token'] = $token;
        return response()->json($array);
        //return response()->json(compact('user','token'),200);
    }

    public function getAuthenticatedUser()
    {
        return $user = JWTAuth::parseToken()->authenticate();
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return $user->name;
        //return response()->json(compact('user'));
    }
}

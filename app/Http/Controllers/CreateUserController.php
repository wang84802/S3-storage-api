<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use DB;
use Illuminate\Support\Facades\Validator;

class CreateUserController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|min:5|confirmed',
            'password_confirmation' => 'required|min:5'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $query = DB::table('users')->where('name','=',$request->name); // Query DB
        return response()->json($query->get(),201);
    }
}
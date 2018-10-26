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
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);

        $query = DB::table('users')->where('name','=',$name); // Query DB
        return $query->get();
    }
}
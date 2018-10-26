<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class testController extends Controller
{
    public function test(Request $request)
    {
        return $request;
    }
}

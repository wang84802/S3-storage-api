<?php
namespace App\Repositories;

use DB;
use Illuminate\Http\Request;

class TokenRepository
{
    public function GetServicebyToken($token)
    {
        return DB::table('api_token')->where('token',$token)->value('service');
    }
};


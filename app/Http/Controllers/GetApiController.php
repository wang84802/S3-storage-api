<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\File;

class GetApiController extends Controller
{
    public function updated_at(){
        $files = File::orderBy('updated_at','asc')->simplepaginate(2);
        return $files;
    }
    public function filename(){
        $files = File::orderBy('name','asc')->simplepaginate(2);
        return $files;
    }
    public function size(){
        $files = File::orderBy('size','asc')->simplepaginate(2);
        return $files;
    }

}

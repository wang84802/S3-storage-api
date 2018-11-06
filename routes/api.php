<?php

use Illuminate\Http\Request;
use App\File;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//Route::get('api_download','ApiController@form');

Route::post('register', 'Auth\RegisterController@register');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout','Auth\LoginController@logout');

Route::group(['middleware' => 'auth:api'], function() {
    Route::post('api_download','ApiController@download');

    Route::post('api_upload','ApiController@upload');

    Route::post('rename','ApiController@rename');

    Route::post('delete','ApiController@delete');
    Route::prefix('orderby')->group(function(){
        Route::post('updated_at', function () {
            //DB::connection()->enableQueryLog();
            $files = File::orderBy('updated_at','asc')->simplepaginate(2);
            //return view('file',compact('files'));
            return $files;
            //$db = DB::getQueryLog();
            //var_dump($db);
        });

        Route::post('filename', function () {
            $files = File::orderBy('name','asc')->simplepaginate(2);
            //return view('file',compact('files'));
            return $files;
        });

        Route::post('size', function () {
            $files = File::orderBy('size','asc')->simplepaginate(2);
            //return view('file',compact('files'));
            return $files;
        });
    });


    Route::post('search',function(Request $request){
        $search = $request->search;
        $files = File::where('name','like','%'.$search.'%')->simplepaginate(2);
        if($files=='[]')
            return response()->json(['message' => 'String not found.']);
        else
        {
            return $files;
        }
    });
});
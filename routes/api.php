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

Route::post('register', 'Auth\RegisterController@register');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout','Auth\LoginController@logout');

Route::group(['middleware' => 'is_user'], function() {
    Route::post('api_upload','taskController@task_upload');
    Route::post('api_download','taskController@task_download');
    Route::post('TestUpload','taskController@TestUpload');
    Route::post('TestDownload','taskController@TestDownload');

    Route::post('rename','PostApiController@rename');
    Route::post('delete','PostApiController@delete');
    Route::post('search','PostApiController@search');

    Route::prefix('orderby')->group(function(){
        Route::get('updated_at','GetApiController@updated_at');

        Route::get('filename','GetApiController@filename');

        Route::get('size','GetApiController@size');
    });


});
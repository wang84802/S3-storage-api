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
Route::post('reg', 'UserController@register');
Route::post('log', 'UserController@authenticate');
Route::get('open', 'DataController@open');

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('closed', 'DataController@closed');
    Route::get('user', 'UserController@getAuthenticatedUser');

    Route::post('TaskUpload','taskController@TestUpload');
    Route::post('TaskDownload','taskController@TestDownload');

    Route::post('rename','PostApiController@rename');
    Route::post('delete','PostApiController@delete');
    //Route::get('show1','GetApiController@show');
});

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::post('register', 'Auth\RegisterController@register');
//Route::post('login', 'Auth\LoginController@login');
//Route::post('logout','Auth\LoginController@logout');

Route::get('search','GetApiController@search');

Route::group(['middleware' => 'is_user'], function() {
    Route::post('api_upload','taskController@task_upload');
    Route::post('api_download','taskController@task_download');

//    Route::post('hard_delete','PostApiController@hard_delete');
//    Route::post('restore','PostApiController@restore');
//
//    Route::get('search','GetApiController@search');
//    Route::get('show','GetApiController@show');
//    Route::get('recycle_bin','GetApiController@recycle_bin');
//
//    Route::prefix('orderby')->group(function(){
//        Route::get('updated_at','GetApiController@updated_at');
//        Route::get('filename','GetApiController@filename');
//        Route::get('size','GetApiController@size');
//    });


});
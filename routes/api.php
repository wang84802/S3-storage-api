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

Route::group(['middleware' => 'is_api'], function() {
    Route::post('TaskUpload','taskController@TestUpload');
    Route::post('TaskDownload','taskController@TestDownload');
    Route::post('rename','PostApiController@rename');
    Route::post('delete','PostApiController@delete');
});

Route::post('refresh','PostApiController@refresh');
Route::group(['middleware' => ['jwt.verify']], function() {
//    Route::post('TaskUpload','taskController@TestUpload');
//    Route::post('TaskDownload','taskController@TestDownload');
//    Route::post('rename','PostApiController@rename');
//    Route::post('delete','PostApiController@delete');
    Route::get('closed', 'DataController@closed');
    Route::get('user', 'UserController@getAuthenticatedUser');
    //Route::get('show1','GetApiController@show');
});

Route::get('search','GetApiController@search');
Route::group(['middleware' => 'is_user'], function() {
    Route::post('api_upload','taskController@task_upload');
    Route::post('api_download','taskController@task_download');
});
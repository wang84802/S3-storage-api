<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Http\Request;
use App\File;

//use DB;

Route::middleware('is_admin')->post('/admin_create','CreateUserController@create');

Route::get('/', function () {
    return view('welcome');
});
Route::post('zip','PostApiController@zip');
Route::post('restore','PostApiController@restore');
Route::get('show','PostApiController@show');

//API User
Route::post('test','PostApiController@test');


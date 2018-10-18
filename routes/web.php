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
//use DB;
Route::get('/upload', 'UploadController@uploadForm')->name('uploadForm');
Route::post('/upload', 'UploadController@uploadSubmit');
Route::post('/flush','UploadController@flush');

Route::get('create','StorageController@create');
Route::post('create','StorageController@store');

Route::get('download','StorageController@showS3')->name('showS3');
Route::post('download', 'StorageController@download');

Route::post('api_download','ApiController@download');
Route::get('api_download','ApiController@form');

Route::post('api_upload','ApiController@upload');

Route::get('download/response',function(){
    return response()->download(storage_path('app/temp.txt'),'test.txt');
});

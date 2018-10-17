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

Route::get('/string' ,function(){

    /*
   $a = ['a','b','c','d'];

   list($foo) = array_splice($a,1,4);
   return $foo;
   echo 1;
   //echo ($a[0].$a[1]);
   $count = count($a)-1;
   function rotate($array,$r1,$r2)
   {
       $temp = $array[$r1];
       $array[$r1] = $array[$r2];
       $array[$r2] = $temp;
       return $array;
   }
   return rotate($a,0,2);

   
   if($count == 1)
       return $a[$count];
   else {

   }
   */


});


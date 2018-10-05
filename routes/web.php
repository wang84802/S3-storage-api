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

Route::get('/', function () {
    return view('welcome');
});


Route::get('/upload', 'UploadController@uploadForm');
Route::post('/upload', 'UploadController@uploadSubmit');

Route::get('create','StorageController@create');
Route::post('create','StorageController@store');
//Route::post('create','StorageController@imageupload');

/*
Route::post('create',function (Request $request){
    $photos = $request->file('profile_image');
    $paths  = [];

    foreach ($photos as $photo) {
        $extension = $photo->getClientOriginalExtension();
        $filename  = 'profile-photo-' . time() . '.' . $extension;
        $paths[]   = $photo->storeAs('profile_image', $filename);
    }

    dd($paths);
});
*/

Route::get('list','StorageController@list');


Route::resource('documents','DocumentController');

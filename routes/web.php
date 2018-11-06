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

Route::middleware('is_admin')->post('/admintest','CreateUserController@create');

Route::get('/', function () {
    return view('welcome');
});
Route::post('zip','ApiController@zip');

/*
Route::get('/',function(){
    $files = File::orderBy('updated_at','asc')->paginate(1);
    return view('welcome',compact('files'));
});
*/

/*
Route::get('/upload', 'UploadController@uploadForm')->name('uploadForm');
Route::post('/upload', 'UploadController@uploadSubmit');
Route::post('/flush','UploadController@flush');

Route::get('create','StorageController@create');
Route::post('create','StorageController@store');

Route::get('download','StorageController@showS3')->name('showS3');

Route::post('download', 'StorageController@download');

Route::get('api_download','ApiController@form');
*/
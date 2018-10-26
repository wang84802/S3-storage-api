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

Route::middleware('is_admin')
    ->post('/admintest','CreateUserController@create');



Route::get('files', function () {
    $files = File::orderBy('created_at','asc')->paginate(1);
    return view('file')->with(['Files' => $files]);
});

Route::post('search',function(Request $request){
    $search = $request->search;
    $files = File::where('name','like',$search.'%')->get();
    return $files;
});

/*
Route::get('string',function(){
    $s='aab';
    $a = substr($s,-1);
    $b = substr($s, 0,3);
    //return substr($s,0,strlen($s)-1);

    $length = 1;
    $temp = 0;

    if(strlen($s)==0)
        return 0;
    $zero = 0;
    for($i=0;$i<strlen($s);$i++){
        for($j=$i-1;$j>=0;$j--) {
            $string = substr($s, 0, $i);

            echo $string.$s[$i].'<br>';
            $key = strpos($string, $s[$i]);

            if($key) {
                $zero = $i;
                echo $zero.'<br>';
                break;
            }else{
                $temp = $i-$zero+1;
                //echo $i.' '.$j.' '.$zero.'<br>';
            }
            if($temp>$length)
                $length = $temp;
        }

    }
    return $length;


});
Route::get('/upload', 'UploadController@uploadForm')->name('uploadForm');
Route::post('/upload', 'UploadController@uploadSubmit');
Route::post('/flush','UploadController@flush');

Route::get('create','StorageController@create');
Route::post('create','StorageController@store');

Route::get('download','StorageController@showS3')->name('showS3');

Route::post('download', 'StorageController@download');

Route::get('api_download','ApiController@form');
*/
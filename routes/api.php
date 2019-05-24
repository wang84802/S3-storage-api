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

Route::group(['middleware' => 'is_api'], function() {
    Route::post('Upload','UploadController@Upload');
    Route::post('Download','DownloadController@Download');
    Route::post('BulkUpload','BulkUploadController@BulkUpload');
    Route::post('BulkDownload','BulkDownloadController@BulkDownload');

    Route::post('TaskUpload','taskController@TestUpload');
    Route::post('TaskDownload','taskController@TestDownload');
    //Route::post('BulkDownload','taskController@BulkDownload');
    Route::post('rename','PostApiController@Rename');
    Route::post('delete','PostApiController@Delete');
    Route::post('Bulkdelete','PostApiController@Bulkdelete');
});

//    Route::post('TaskUpload','taskController@TestUpload');
//    Route::post('TaskDownload','taskController@TestDownload');
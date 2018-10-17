<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;
use URL;
use DB;
use League\Flysystem\Util\ContentListingFormatter;

class StorageController extends Controller
{
	public function create()//get create
	{
        return view('uploadimage');
	}

    public function store(Request $request)//post create
    {
        if($request->hasFile('profile_image')) {
            $files = $request->file('profile_image');
            $path = [];
            foreach($files as $file){

            }
            //get filename with extension
            $filenamewithextension = $request->file('profile_image')->getClientOriginalName();

            dd($filenamewithextension);
        }
    }
    public function showS3()//get download
    {
        //return Storage::disk('s3')->allFiles();
        return view('show_S3');
    }
    public function download(Request $request)//post download
    {
        /*check database has record or not
        $IfExist = DB::table('products_photos')->where('filename','=',$request->file_name)->first();
        if(!empty($IfExist)){
            return $IfExist->filename;
        }else{
            return "file doesn't exist!";
        }
        */

        ///*download file
        //$file_name = $request->input('data.filename'); //from post body
        $file_name = $request->get('file_name');         //from input form
        return Storage::disk('s3')->download($file_name);

        /*make folder
        Storage::disk('s3')->makeDirectory('test');
        Storage::disk('s3')->makeDirectory($file_name);
        */

        //Storage::disk('s3')->put('test/456.txt','test/456');

    }
    public function delete()
    {
        Storage::disk('s3')->delete('456.txt');
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;
use URL;
use League\Flysystem\Util\ContentListingFormatter;

class StorageController extends Controller
{
	public function create()
	{
	    //Storage::disk('s3')->put('456.txt','456');

	    //return "success";
        //$contents = Storage::disk('s3')->get('456.txt');
	    //print $contents;
	    //return Storage::disk('s3')->download('456.txt');
        //$url = Storage::disk('s3')->url('456.txt');
        //return $url;
        return view('uploadimage');
	}
	public function imageupload(Request $request)
    {
        $files = $request->file('profile_image');
        $paths  = [];

        foreach($files as $file){
            $extension = $file->getClientOriginalExtension();
            $filename  = 'profile-photo-' . time() . '.' . $extension;
            $paths[]   = $file->storeAs('profile_image', $filename);
        }


        dd($paths);
        //foreach($files as $file){
        //    echo $file;
        //}
    }
    public function store(Request $request)
    {


        if($request->hasFile('profile_image')) {

            $files = $request->file('profile_image');
            $path = [];
            foreach($files as $file){

            }
            //get filename with extension
            $filenamewithextension = $request->file('profile_image')->getClientOriginalName();

            dd($filenamewithextension);

            ///Storage::disk('s3')->put($filenamewithextension, fopen($request->file('profile_image'), 'r+'), 'public');

            //return redirect('create')->with('message','File uploaded successfully.');

        }
    }
    public function showS3()
    {
        //return Storage::disk('s3')->allFiles();
        return view('show_S3');

    }
    public function download(Request $request)
    {
        //$list = Storage::disk('s3')->files();
        $url = Storage::disk('s3')->url('456.txt');
        //return $list;
        //return $url;


        $file_name = $request->get('file_name');
        //echo gettype($file_name);
        return Storage::disk('s3')->download($file_name);


        //Storage::disk('s3')->makeDirectory('test');
        //Storage::disk('s3')->makeDirectory($file_name);

        //Storage::disk('s3')->put('test/456.txt','test/456');

    }
    public function delete()
    {
        Storage::disk('s3')->delete('456.txt');
    }

}

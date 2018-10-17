<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Storage;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public $successStatus = 200;
    public function form()
    {
        return view('form');
    }
    public function download(Request $request)//api_download
    {
        /*testing area
        //$file_name = 'hello.txt';
        $file_url = Storage::disk('s3')->url('hello.txt');
        return response()->download(storage_path('app/temp.txt'));
        */
        //$file_name = $request->get('file_name');
        return Storage::disk('s3')->download('API.PNG');

        //API of single file download
        /*
        //$name = $request->input('date.filename');
        $i = 1;
        $name = $request->input('date.filename'.$i);
        $hasfile = Storage::disk('s3')->has($name);
        if ($hasfile) {
            return Storage::disk('s3')->download($name);
        } else {
            return "File isn't exist!";
        }
        */
    }
    public function upload(Request $request)//api_upload
    {
        //API of multiple files upload
        ///*
        $input= $request->all();
        $file = $input['uploads'];
        $i=0;

        if (!empty($file)) {
            foreach ($file as $files) {
                $name = $file[$i]->getClientOriginalName();

                Storage::disk('s3')->put($name,file_get_contents($file[$i]));
                //return $base64_e = base64_encode($file[$i]);
                //return $base64_d = base64_decode($base64_e);

                $hasfile = Storage::disk('s3')->has($name); //check record in S3
                if ($hasfile) {
                    echo  nl2br($name." upload successful!\n");
                } else {
                    echo nl2br($name." upload failed!\n");
                }
                $i++;

            }
        }
        //*/

        //API of single file upload
        /*
        $files = $request->file('uploads');
        if(!empty($files)) {
            foreach ($files as $file) {
                $name = $file->getClientOriginalName();
                Storage::disk('s3')->put($name, file_get_contents($file));

                $hasfile = Storage::disk('s3')->has($name); //check record in S3
                if ($hasfile) {
                    return "Success!";
                } else {
                    return "Failed!";
                }
            }
        } else {
            echo "Please choose file.";
        }

        */
    }
}

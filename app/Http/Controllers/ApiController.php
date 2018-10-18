<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Storage;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    public $successStatus = 200;
    public function form()
    {
        return view('form');
    }
    public function download(Request $request)//api_download
    {

        $re = Storage::disk('s3')->download('API.PNG');

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
        //API of multiple files upload (body with filename & filecontent)
        ///*
        $name = $request->input('date');
        $i = count($name)-1;
        for($j = 0 ; $j <= $i ; $j ++ ) {
            $filename[$j] = $name[$j]['filename'];

            $filecontent[$j] = $name[$j]['filecontent'];

            Storage::disk('s3')->put($filename[$j],base64_decode($filecontent[$j]));
        }

        //check records in S3
        for ($k = 0 ; $k <= $i ; $k ++ ) {
            $hasfile = Storage::disk('s3')->has($filename[$k]);
            if ($hasfile) {
                echo $filename[$k]." Upload Success!".'<br>';
            } else {
                echo $filename[$k]." Upload Failed!".'<br>';;
            }
        }
        //*/


        //API of multiple files upload
        /*
        $input= $request->all();
        $file = $input['uploads'];
        $i=0;

        if (!empty($file)) {
            foreach ($file as $files) {
                $name = $file[$i]->getClientOriginalName();

                //

                $hasfile = Storage::disk('s3')->has($name); //check record in S3
                if ($hasfile) {
                    echo  nl2br($name." upload successful!\n");
                } else {
                    echo nl2br($name." upload failed!\n");
                }
                $i++;

            }
        }
        */

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

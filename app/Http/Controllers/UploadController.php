<?php

namespace App\Http\Controllers;

use Storage;
use App\Product;
use App\ProductsPhoto;
use Illuminate\Http\Request;
use App\Http\Requests\UploadRequest;

class UploadController extends Controller
{
    public function uploadForm()
    {
        return view('upload_form');
    }

    public function uploadSubmit(Request $request)
    {
        $input= $request->all();
        //dd($input);
        $file = $input['photos'];
        $i = 0;
        //dd($file);
        foreach ($request->photos as $photo) {

            $name = $file[$i]->getClientOriginalName();

            Storage::disk('s3')->put($name,fopen(iconv('UTF-8','GBK',$file[$i]), 'r+'),'public');
            $i++;
            //return Storage::disk('s3')->size('456.txt').' bytes';
        }
        //return 'Upload successful!';
    }
}

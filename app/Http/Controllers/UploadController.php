<?php

namespace App\Http\Controllers;

use Storage;
use App\Product;
use App\ProductsPhoto;
use Illuminate\Http\Request;
use App\Http\Requests\UploadRequest;
use Illuminate\Route\Redirector;
use Session;
use DB;

class UploadController extends Controller
{
    public function flush()
    {
        Session()->flush();
        return redirect()->back();
    }
    public function uploadForm()
    {
        return view('upload_form');
    }

    public function uploadSubmit(Request $request)
    {
        //file put in S3
        $input= $request->all();
        $file = $input['photos'];
        $i = 0;

        //file put in database
        $product = Product::create($request->all());


        //getClientMimeType getClientSize
        foreach ($request->photos as $photo) {
            $name = $file[$i]->getClientOriginalName();
            
            if($IfExist = DB::table('products_photos')
                ->where('filename','=',$name)
                ->get())
            {
                DB::table('products_photos')->where('filename','=',$name)->delete(); //要改成soft delete
            }

            //file put in S3
            $name = $file[$i]->getClientOriginalName();
            Storage::disk('s3')->put($name,fopen(iconv('UTF-8','GBK',$file[$i]), 'r+'),'public');
            $i++;

            //file put in database
            $filename = $photo->store('photos');
            ProductsPhoto::create([
                'product_id' => $product->id,
                'filename' => $name
            ]);
        }

        //return  ProductsPhoto::get('filename');
        Session()->put('status','success');

        //dd($filedatabase);
        //return redirect()->back()->with($filedatabase);
        //return redirect()->route('uploadForm');
        //return Storage::disk('s3')->size('456.txt').' bytes';
    }
}

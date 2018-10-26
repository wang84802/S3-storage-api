<?php

namespace App\Http\Controllers;
use App\User;
use App\File;
use DB;
use Illuminate\Support\Facades\Auth;
use Validator;
use Storage;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ApiController extends Controller
{

    public $successStatus = 200;
    public function form()//api_download
    {
        return view('form');
    }
    public function download(Request $request)//api_download
    {

        $name = $request->input('data.filename');
        if($name == null){
            $name = $request->input('file_name');
        }
        if( Storage::disk('s3')->exists($name) ) {
            $file =  Storage::disk('s3')->get($name);


            $file = base64_encode($file);

            $headers = [
                'Content-Type' => 'your_content_type',
                'Content-Description' => 'File Transfer',
                'Content-Disposition' => "attachment; filename={$name}",
                'filename'=> $name
            ];
            return response()->make($file, 200, $headers);

        }
        else{
            abort(404, 'File does not exist in S3.');
        }


        //API of single file download
        /*
        $name = $request->input('data.filename');
        if($name == null){
            $name = $request->input('file_name');
        }
        return $name;
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
        $name = $request->input('data');
        $api = $request->input('api_token');

        $username = DB::table('users')->where('api_token',$api)->value('name'); //get username by token
        $i = count($name)-1;
        for( $j = 0 ; $j <= $i ; $j ++ ) {
            $FileName[$j] = $name[$j]['filename'];
            $Extension[$j] = $name[$j]['extension'];
            $FileWithExtension[$j] = $FileName[$j].'.'.$Extension[$j];
            $content[$j] = $name[$j]['content'];

            Storage::disk('s3')->put($FileWithExtension[$j],base64_decode($content[$j]));

            $Size[$j] = Storage::disk('s3')->size($FileWithExtension[$j]);  //store name,extension,size

            File::create([   //Put record in database
                'name' => $FileName[$j],
                'extension' => $Extension[$j],
                'size' => $Size[$j],
                'created_by' => $username,
                'updated_by' => $username,
            ]);
        }
        //check records in S3
        for ($k = 0 ; $k <= $i ; $k ++ ) {
            $hasfile = Storage::disk('s3')->has($FileWithExtension[$k]);
            if ($hasfile) {
                echo $FileWithExtension[$k]." Upload Success!".'<br>';
            } else {
                echo $FileWithExtension[$k]." Upload Failed!".'<br>';
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
    public function rename(Request $request)//rename
    {
        $name = $request->input('data');
        $i = count($name)-1;
        for( $j = 0 ; $j <= $i ; $j++ ) { //get all request
            $OriginalName[$j] = $name[$j]['origin'];
            $ReName[$j] = $name[$j]['rename'];
            $Extension[$j] = $name[$j]['extension'];
            $O_FileWithExtension[$j] = $OriginalName[$j] . '.' . $Extension[$j];
            $R_FileWithExtension[$j] = $ReName[$j] . '.' . $Extension[$j];
        }
        for( $j = 0 ; $j <= $i ; $j++){
            $hasfile = Storage::disk('s3')->has($O_FileWithExtension[$j]);
            if (!$hasfile)
                abort(404, $O_FileWithExtension[$j]." does not exist!");
        }
        for( $j = 0 ; $j <= $i ; $j++ ){
            Storage::disk('s3')->move($O_FileWithExtension[$j],$R_FileWithExtension[$j]);//s3

            File::where('name',$OriginalName[$j])->update(array(//database
                'name' => $ReName[$j],
                'extension' => $Extension[$j],
                'updated_at' => now()
            ));
        }
        for( $j = 0 ; $j <= $i ; $j++){
            $hasfile = Storage::disk('s3')->has($R_FileWithExtension[$j]);
            if ($hasfile)
                echo $R_FileWithExtension[$j].' rename successfully!'."<br/>";
            else
                abort(404, $R_FileWithExtension[$j]." rename unsuccessfully!");
        }
    }
    public function delete(Request $request)
    {
        $filename = $request->input('filename');

        $files = File::where('name',$filename)->get();
        $delete_files = File::where('name',$filename);
        if($files=='[]'){
            abort(404, $filename.' does not exist in database.');
        }else{
            $delete_files->delete();
        }

        if(File::withTrashed()->where('name',$filename)->get()){ // force softdelete objects to show
            echo $filename.' delete successfully!';
        }else{
            echo $filename.' delete unsuccessfully!';
        }
    }

}

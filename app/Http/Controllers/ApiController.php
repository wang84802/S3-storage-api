<?php

namespace App\Http\Controllers;
use App\File;
use DB;
use Validator;
use Storage;
use Illuminate\Http\Request;
use Zipper;

class ApiController extends Controller
{
    public function zip(Request $request)
    {
        $S3_url = "https://chris-storage-api.s3.ap-southeast-1.amazonaws.com/";
        $zipfile = $request->input('zipfile');
        $name = $request->input('data');
        $count = count($name)-1;
        for($i=0;$i<=$count;$i++){
            if(!Storage::disk('s3')->exists($name[$i]['filename']))
                abort(404, $name[$i]['filename']. ' does not exist in S3.');
        }

        $zip = new \ZipArchive();
        $zip->open('/var/www/html/S3/public/'.$zipfile, \ZipArchive::CREATE);
        for($i=0;$i<=$count;$i++){
            $zip->addFromString($name[$i]['filename'],file_get_contents($S3_url.$name[$i]['filename']));
        }
        $zip->close();

        header('Content-disposition: attachment; filename='.$zipfile);
        header('Content-type: application/zip');
        readfile('/var/www/html/S3/public/'.$zipfile);

    }
    public function form()//api_download
    {
        return view('form');
    }
    public function download(Request $request)//api_download
    {
        //API of single file download(filename in request)
        $name = $request->input('data.filename');
        if($name == null){
            $name = $request->input('file_name');
            return $name;
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
        else
            abort(404, 'File does not exist in S3.');

    }

    public function upload(Request $request)//api_upload
    {
        //API of multiple files upload (filename & filecontent in request)
        ///*
        $name = $request->input('data');
        $api = $request->input('api_token');

        $username = DB::table('users')->where('api_token',$api)->value('name'); //get username by token
        $i = count($name)-1;
        for( $j = 0 ; $j <= $i ; $j ++ ) {
            $FileName[$j] = $name[$j]['filename'];

            File::where('name',$FileName[$j])->delete(); // file soft-delete

            $Extension[$j] = $name[$j]['extension'];
            $FileWithExtension[$j] = $FileName[$j].'.'.$Extension[$j];
            $content[$j] = $name[$j]['content'];

            Storage::disk('s3')->put($FileWithExtension[$j],base64_decode($content[$j]));  //upload

            $Size[$j] = Storage::disk('s3')->size($FileWithExtension[$j]);  //get file size

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
            if ($hasfile)
                echo $FileWithExtension[$k]." Upload Success!".'<br>';
             else
                echo $FileWithExtension[$k]." Upload Failed!".'<br>';

        }
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
        for( $j = 0 ; $j <= $i ; $j++){ //check record in S3 before rename
            $hasfile = Storage::disk('s3')->has($O_FileWithExtension[$j]);
            if (!$hasfile)
                abort(404, $O_FileWithExtension[$j]." does not exist!");
        }
        for( $j = 0 ; $j <= $i ; $j++ ){ //update S3
            Storage::disk('s3')->move($O_FileWithExtension[$j],$R_FileWithExtension[$j]);

            File::where('name',$OriginalName[$j])->update(array( //update database
                'name' => $ReName[$j],
                'extension' => $Extension[$j],
                'updated_at' => now()
            ));
        }

        for( $j = 0 ; $j <= $i ; $j++){ //check record in S3 after rename
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
        }else
            $delete_files->delete();
        

        if(File::withTrashed()->where('name',$filename)->get()){ // force softdelete objects to show
            echo $filename.' delete successfully!';
        }else
            echo $filename.' delete unsuccessfully!';

    }
}

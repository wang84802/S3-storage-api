<?php

namespace App\Http\Controllers;

use Exception;
use Storage;
use App\File;
use App\Document;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Repositories\FileRepository;
use Zipper;
use ZipArchive;
use App\Events\OrderShipped;

class PostApiController extends Controller
{
    protected $userRepository,$fileRepository;
    public function __construct(UserRepository $userRepository,FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->userRepository = $userRepository;
    }
    public function test()
    {
        try {
            $error = 'Always throw this error';
        if (1)
            throw new Exception($error);

        echo 'Never executed';
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(),'<br>';
        }
    }
    public function show()
    {
        return $this->fileRepository->Show();
    }
    public function zip(Request $request)//zip : download with zipfile
    {
        $S3_url = "https://chris-storage-api.s3.ap-southeast-1.amazonaws.com/";
        $zipfile = $request->input('zipfile');
        $name = $request->input('data');
        $stream = fopen($S3_url.$name[0]['filename'],'r');
        //return $stream;
        return stream_get_contents($stream);

        $count = count($name)-1;
        for($i=0;$i<=$count;$i++){
            if(!Storage::disk('s3')->exists($name[$i]['filename']))
                abort(404, $name[$i]['filename']. ' does not exist in S3.');
        }

        $zip = new \ZipArchive();
        $zip->open('/var/www/html/S3/public/'.$zipfile, \ZipArchive::CREATE);

        for($i=0;$i<=$count;$i++){
            $zip->addFromString($name[$i]['filename'],base64_encode(file_get_contents($S3_url.$name[$i]['filename'])));
        }
        $zip->close();

        header('Content-disposition: attachment; filename='.$zipfile);
        header('Content-type: application/zip');
        $content = readfile('/var/www/html/S3/public/'.$zipfile);
        //return $content;
        Storage::disk('s3')->put('test1.zip',(string)$content);
        unlink(public_path($zipfile));
    }
    public function rename(Request $request)//rename
    {
        $api = $request->header('Api-Token');
        $username = $this->userRepository->getNameByToken($api);

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
            $hasfile = $this->fileRepository->GetFile($OriginalName[$j],$Extension[$j])->get();
            if ($hasfile=='[]')
                abort(404, $O_FileWithExtension[$j]." does not exist!");
        }
        for( $j = 0 ; $j <= $i ; $j++ ){ //update S3
            Storage::disk('s3')->move($O_FileWithExtension[$j],$R_FileWithExtension[$j]);

            $this->fileRepository->Rename($OriginalName[$j],$ReName[$j],$Extension[$j],now(),$username);
        }

        for( $j = 0 ; $j <= $i ; $j++){ //check record in S3 after rename
            $hasfile = $this->fileRepository->GetFile($ReName[$j],$Extension[$j])->get();
            if($hasfile!=='[]')
                echo $R_FileWithExtension[$j].' rename successfully!'."<br/>";
            else
                abort(404, $R_FileWithExtension[$j]." rename unsuccessfully!");
        }
    }
    public function delete(Request $request)
    {
        $filename = $request->input('filename');
        $extension = $request->input('extension');
        $delete_files = $this->fileRepository->GetFile($filename,$extension);
        //return $delete_files->get();
        if($delete_files->get()=='[]'){
            abort(404, $filename.'.'.$extension.' does not exist.');
        }else
            $delete_files->delete();

        if($this->fileRepository->GetFilewithTrashed($filename,$extension)){ // force softdelete objects to show
            echo $filename.'.'.$extension.' delete successfully!';
        }else
            echo $filename.'.'.$extension.' delete unsuccessfully!';
    }
    /*
    public function restore(Request $request)
    {
        $filename = $request->input('filename');
        $files = File::withTrashed()->where('name',$filename)->restore();
    }
    */
    public function search(Request $request)
    {
        $search = $request->search;
        $files = $this->fileRepository->Search($search);

        if($files->get()=="[]")
            return response()->json(['message' => 'String not found.'],404);
        else
            return $files->simplepaginate(2);
    }
//    public function upload(Request $request)//api_upload
//    {
//        //API of multiple files upload (filename & filecontent in request)
//        ///*
//        $name = $request->all();
//        $name = $name['data'];
//        $api = $request->header('Api-Token');
//        $username = $this->userRepository
//            ->getNameByToken($api);
//
//        $i = count($name)-1;
//        for( $j = 0 ; $j <= $i ; $j ++ ) {
//            $FileName[$j] = $name[$j]['filename'];
//
//            $this->fileRepository->Delete($FileName[$j]); //soft-delete
//
//            $Extension[$j] = strtolower($name[$j]['extension']);
//            $FileWithExtension[$j] = $FileName[$j].'.'.$Extension[$j];
//            $content[$j] = $name[$j]['content'];
//
//            Storage::disk('s3')->put($FileWithExtension[$j],base64_decode($content[$j]));  //upload
//
//            $size[$j] = Storage::disk('s3')->size($FileWithExtension[$j]);  //get file size
//
//            $this->fileRepository->File($FileName[$j],$Extension[$j],$size[$j],$username,$username); //create file
//        }
//        //check records in S3
//        for ($k = 0 ; $k <= $i ; $k ++ ) {
//            $hasfile = Storage::disk('s3')->has($FileWithExtension[$k]);
//            if ($hasfile)
//                echo $FileWithExtension[$k]." Upload Success!".'<br>';
//             else
//                echo $FileWithExtension[$k]." Upload Failed!".'<br>';
//        }
//    }
//    public function download(Request $request)//api_download
//    {
//        //API of single file download(filename in request)
//        $name = $request->input('data.filename');
//        if( Storage::disk('s3')->exists($name) ) {
//
//            $file =  Storage::disk('s3')->get($name);
//            Storage::disk('s3')->put('API_upload.PNG',$file);
//            return base64_encode($file);
//            //$file = base64_encode($file);
//            $headers = [
//                'Content-Type' => 'your_content_type',
//                'Content-Description' => 'File Transfer',
//                'Content-Disposition' => "attachment; filename={$name}",
//                'filename'=> $name
//            ];
//            return response()->make($file, 200, $headers);
//            //return response()->JSON([
//            //    'filename' => $name,
//            //   'content' => $file,
//            //]);
//        }
//        else
//            abort(404, 'File does not exist in S3.');
//    }
}

<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Storage;
use App\File;
use App\Document;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Repositories\FileRepository;
use Zipper;
use ZipArchive;
use App\Events\OrderShipped;
use Log;
class PostApiController extends Controller
{
    protected $userRepository,$fileRepository;
    public function __construct(UserRepository $userRepository,FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->userRepository = $userRepository;
    }
    public function UploadString(Request $request)
    {
        $content = base64_encode(file_get_contents($request->file('name')->getRealPath()));
        $array = array();
        for($i = 0;$i<=1;$i++)
        {
            $array['data'][$i] = array(
                'filename' => 'test'.($i+1),
                'extension' => 'txt',
                'content' => $content
            );

        }
        return $array;
    }
    public function test(Request $request)
    {
        $a = microtime(true);
        $name = $request->file('name')->getClientOriginalName();
        $content = (file_get_contents($request->file('name')->getRealPath()));
        Storage::disk('s3')->put($name,$content);
        $b = microtime(true);
        Log::info('Test file upload time:'.$b.' '.$a);
        return 0;
        try {
            $error = 'Always throw this error';
        if (1)
            throw new Exception($error);
        echo 'Never executed';
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(),'<br>';
        }
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
                response($O_FileWithExtension[$j]." does not exist!",404);
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
                response( $R_FileWithExtension[$j]." rename unsuccessfully!",404);
        }
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $files = $this->fileRepository->Search($search);

        if($files->get()=="[]")
            return response()->json(['message' => 'String not found.'],404);
        else
            return $files->simplepaginate(2);
    }

    public function delete(Request $request)
    {
        $api = $request->header('Api-Token');
        $username = $this->userRepository->getNameByToken($api);
        $data = $request->input('data');
        $i = count($data)-1;
        for( $j = 0 ; $j <= $i ; $j++ ) {
            $filename = $data[$j]['filename'];
            $extension = $data[$j]['extension'];
            $delete_files = $this->fileRepository->GetFile($filename, $extension);
            if ($delete_files->get() == '[]') {
                response( $filename . '.' . $extension . ' does not exist.',404);
            } else {
                $this->fileRepository->UpdateName($filename, $extension, $username);
                $delete_files->delete();
            }
            if ($this->fileRepository->GetFileOnlyTrashed($filename, $extension)) { // show softdelete objects
                echo $filename . '.' . $extension . ' delete successfully!'.'<br>';
            } else {
                echo $filename . '.' . $extension . ' delete unsuccessfully!'.'<br>';
            }
        }
    }

    public function restore(Request $request)
    {
        $api = $request->header('Api-Token');
        $username = $this->userRepository->getNameByToken($api);
        $data = $request->data;
        $i = count($data)-1;
        for( $j = 0 ; $j <= $i ; $j++ ) {
            $id = $data[$j]['id'];
            $filename = $data[$j]['filename'];
            $extension = $data[$j]['extension'];
            $result = $this->fileRepository->GetFileOnlyTrashed($id,$filename,$extension);
            if ($result->get()=='[]')
                return response($filename.'.'.$extension.' does not exist!',404);
            else
            {
                $result->update(['updated_by'=>$username]);
                $result->restore();
            }

            if ($result) { // force softdelete objects to show
                echo $filename . '.' . $extension . ' restore successfully!'.'<br>';
            } else {
                echo $filename . '.' . $extension . ' restore unsuccessfully!'.'<br>';
            }
        }
    }
    public function hard_delete(Request $request)
    {
        $data = $request->data;
        $i = count($data)-1;
        for( $j = 0 ; $j <= $i ; $j++ ) {
            $id = $data[$j]['id'];
            $filename = $data[$j]['filename'];
            $extension = $data[$j]['extension'];
            $result = $this->fileRepository->GetFileOnlyTrashed($id,$filename,$extension);
            if ($result->get() == '[]')
                return response( $filename . '.' . $extension . ' does not exist!',404);
            else
            {
                $result->forceDelete();
                Storage::disk('s3')->delete($filename . '.' . $extension);
            }


            if (!$result) { // force softdelete objects to show
                echo $filename . '.' . $extension . ' hard_delete successfully!'.'<br>';
            } else {
                echo $filename . '.' . $extension . ' hard_delete unsuccessfully!'.'<br>';
            }

        }
    }
}

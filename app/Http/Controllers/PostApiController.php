<?php

namespace App\Http\Controllers;

use Exception;
use DB;
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

        $cart = array(
            'data' => array(
                array(
                    'filename' => 'test1.txt',
                    'content' => 'serwerwe'
                ),
                array(
                    'filename' => 'test2.txt',
                    'content' => 'werwerar'
                )
            ),
        );
        $cart['data'][2]['filename'] = '1';
        //$cart = json_encode($cart);
        return $cart;
        //return response()->json(['data'=> ['filename'=>'test1']   ]);

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
        $api = $request->header('Api-Token');
        $username = $this->userRepository->getNameByToken($api);
        $filename = $request->input('filename');
        $extension = $request->input('extension');
        $delete_files = $this->fileRepository->GetFile($filename,$extension);
        if($delete_files->get()=='[]'){
            abort(404, $filename.'.'.$extension.' does not exist.');
        }else
        {
            $this->fileRepository->UpdateName($filename,$extension,$username);
            $delete_files->delete();
        }
        if($this->fileRepository->GetFilewithTrashed($filename,$extension)){ // force softdelete objects to show
            echo $filename.'.'.$extension.' delete successfully!';
        }else
            echo $filename.'.'.$extension.' delete unsuccessfully!';
    }

    public function restore(Request $request)
    {
        $filename = $request->input('filename');
        $result = File::withTrashed()->where('name',$filename)->first();
        $result->restore();
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
}

<?php

namespace App\Http\Controllers;

use DB;
use App;
use Illuminate\Support\Facades\Log;
use Storage;
use Validator;
use App\Document;
use App\Events\OrderShipped;
use Illuminate\Http\Request;
use App\Http\Requests\RenameRequest;
use App\Http\Requests\DownloadRequest;
use App\Repositories\SeqRepository;
use App\Repositories\UserRepository;
use App\Repositories\FileRepository;
use App\Repositories\TokenRepository;

class PostApiController extends Controller
{
    public function __construct(TokenRepository $TokenRepository,UserRepository $userRepository,FileRepository $fileRepository,SeqRepository $SeqRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->userRepository = $userRepository;
        $this->TokenRepository = $TokenRepository;
        $this->SeqRepository = $SeqRepository;
    }
    public function UploadString(Request $request)
    {
        $array =[];
        $array['status'] = 200;
        $array['data'] = array();
        $input = array('status'=>'succeed','file1'=>'123');
        $input2 = array('status'=>'succeed','file1'=>'1234');
        array_push($array['data'],$input);
        array_push($array['data'],$input2);
        return $array;
    }
    public function rename(RenameRequest $request)//rename
    {
        $array = array();
        $array['status'] = 200;
        $api = $request->header('Api-Token');
        $servicename =$this->TokenRepository->GetServicebyToken($api);
        $data = $request->input('data');

        $uni_id = $data['uni_id'];
        $original_name = $this->fileRepository->GetFileNamebyUniid($uni_id);
        $rename = $data['rename'];

        $hasfile = $this->fileRepository->GetFile($original_name);
        if ($hasfile=='[]')
            return response()->json(
                [
                    'status' => 400,
                    'error' => [[
                        'key' => 'data.uni_id',
                        'code' => '400049104',
                        'message' => 'The uni_id '.$uni_id.' does not exist'
                    ]]
                ]
                , 400);

        $document_seq_id = $this->SeqRepository->Generate_seq(2); //Document ID
        $this->fileRepository->Rename($uni_id,$rename,now(),$servicename);
        $this->fileRepository->Create_Document($document_seq_id,$uni_id,'Rename succeed.',$servicename);

        $hasfile = $this->fileRepository->GetFile($rename);
            if($hasfile!=='[]')
                $array['data'] = array($rename => 'Rename successfully!');
        return $array;
    }
    public function delete(DownloadRequest $request)
    {
        $request->validated();
        $array = array();
        $array['status'] = 200;
        $api = $request->header('Api-Token');
        $servicename =$this->TokenRepository->GetServicebyToken($api);

        $data = $request->input('data');
        $uni_id = $data['uni_id'];
        $filename = $this->fileRepository->GetFileNamebyUniid($uni_id);
        $delete_files = $this->fileRepository->GetFile($filename);
        if ($delete_files=='[]') {
            return response()->json(
                [
                    'status' => 400,
                    'error' => [[
                        'key' => 'data.uni_id',
                        'code' => '400049104',
                        'message' => 'The uni_id '.$uni_id.' does not exist'
                    ]]
                ]
                , 400);
        } else {
            $document_seq_id = $this->SeqRepository->Generate_seq(2); //Document ID
            $this->fileRepository->Create_Document($document_seq_id,$uni_id,'Delete '.$filename.' succeed.',$servicename);
            $this->fileRepository->UpdateName($uni_id,$filename, $servicename);
            $this->fileRepository->DeleteFile($uni_id,$filename);
            Storage::disk('s3')->delete($uni_id);

        }
        if ($this->fileRepository->GetFileOnlyTrashed($filename)) { // show softdelete objects
            $array['data'] = array($filename  => 'Delete successfully!');
        } else {
            $array['data'] = array($filename  => 'Delete unsuccessfully!');
        }

        return $array;
    }
}

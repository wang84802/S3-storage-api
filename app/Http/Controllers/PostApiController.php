<?php

namespace App\Http\Controllers;

use DB;
use Log;
use Storage;
use Validator;
use App\Document;
use App\Jobs\test1;
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
        return 1;
        $a = array(2,3,1,8,5,4,6,7);

        usort($a, function($a, $b){print_r($b);return $a-$b;});
        print_r($a);
        return 0;
        $download = Storage::disk('local')->files('Upload_Pool');
        return $download;
        return $content = base64_encode(file_get_contents($request->file('name')->getRealPath()));
        $document = Document::where('id',2)->first();
        event(new OrderShipped($document));
        return 0;
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
                    'error' => [
                        'message' => 'The uni_id '.$uni_id.' does not exist'
                    ],
                ]
                , 400);


        $document_seq_id = $this->SeqRepository->Generate_seq('select','currval_storage(2,1)'); //Document ID
        $this->fileRepository->Rename($uni_id,$rename,now(),$servicename);
        $this->fileRepository->Create_Document($document_seq_id,$uni_id,'Rename succeed.',$servicename);

        $hasfile = $this->fileRepository->GetFile($rename);
            if($hasfile!=='[]')
                $array['data'] = array($rename => 'Rename successfully!');
            else
                return response()->json(
                    [
                        'status' => 400,
                        'error' => $rename." Rename unsuccessfully!",
                    ]
                    , 400);

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
                    'error' => [
                        'message' => 'The uni_id '.$uni_id . ' does not exist.'
                    ],
                ]
                ,400);
        } else {
            $document_seq_id = $this->SeqRepository->Generate_seq('select','currval_storage(2,1)'); //Document ID
            $this->fileRepository->Create_Document($document_seq_id,$uni_id,'Delete '.$filename.' succeed.',$servicename);
            $this->fileRepository->UpdateName($uni_id,$filename, $servicename);
            $this->fileRepository->DeleteFile($uni_id,$filename);
            Storage::disk('s3')->delete($uni_id.'_'.$filename);

        }
        if ($this->fileRepository->GetFileOnlyTrashed($filename)) { // show softdelete objects
            $array['data'] = array($filename  => 'Delete successfully!');
        } else {
            $array['data'] = array($filename  => 'Delete unsuccessfully!');
        }

        return $array;
    }

    public function restore(Request $request)
    {
        $array = array();
        $array['status'] = 200;
        $api = $request->header('Api-Token');
        $username = $this->userRepository->getNameByToken($api);
        $data = $request->data;
        if($data==NULL)
            return response()->json(
                [
                    'status' => 422,
                    'error' => [
                        'message' => 'Input is required.'
                    ],
                ]
                ,400);
        $i = count($data)-1;
        //return $data[0]['id'];
        for( $j = 0 ; $j <= $i ; $j++ ) {
            $id = $data[$j]['id'];
            $filename = $data[$j]['filename'];
            $extension = $data[$j]['extension'];
            $result = $this->fileRepository->GetFileWithTrashed($id,$filename,$extension);

           if ($result->value('deleted_at')==NULL)
               return response()->json(
                   [
                       'status' => 404,
                       'error' => [
                           'message' => $filename.'.'.$extension.' does not exist!'
                       ],
                   ]
                   ,400);
            else
            {
                if($this->fileRepository->GetFile($filename,$extension)->exists())
                    return response()->json(
                        [
                            'status' => 400,
                            'error' => [
                                'message' => $filename.'.'.$extension." already exist!"
                            ],
                        ]
                        , 400);
                $result->update(['updated_by'=>$username,'deleted_at'=>NULL]);
            }
            if ($result) { // force softdelete objects to show
                $array['data'][$j] = array($filename . '.' . $extension => 'restore successfully!');
            } else {
                $array['data'][$j] = array($filename . '.' . $extension => 'restore unsuccessfully!');
            }
        }
        return $array;
    }
    public function hard_delete(Request $request)
    {
        $array = array();
        $array['status'] = 200;
        $data = $request->data;
        $i = count($data)-1;
        for( $j = 0 ; $j <= $i ; $j++ ) {
            $id = $data[$j]['id'];
            $filename = $data[$j]['filename'];
            $extension = $data[$j]['extension'];
            $result = $this->fileRepository->GetFileWithTrashed($id,$filename,$extension);

            if ($result->get() == '[]')
                return response()->json(
                    [
                        'status' => 404,
                        'error' => [
                            'message' => $filename.'.'.$extension.' does not exist!'
                        ],
                    ]
                    ,400);
            else
            {
                $result->forceDelete();
                Storage::disk('s3')->delete($filename . '.' . $extension);
            }
            if (!$result) { // force softdelete objects to show
                $array['data'][$j] = array($filename . '.' . $extension => 'delete successfully!');
            } else {
                $array['data'][$j] = array($filename . '.' . $extension => 'delete unsuccessfully!');
            }
        }
        return $array;
    }
}

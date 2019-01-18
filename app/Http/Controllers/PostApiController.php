<?php

namespace App\Http\Controllers;

use DB;
use Log;
use Storage;
use App\Document;
use App\Jobs\test1;
use App\Events\OrderShipped;
use Validator;
use App\Repositories\SeqRepository;
use App\Repositories\UserRepository;
use App\Repositories\FileRepository;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class PostApiController extends Controller
{
    protected $userRepository,$fileRepository;
    public function __construct(UserRepository $userRepository,FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->userRepository = $userRepository;
    }
    public function refresh(Request $request)
    {
        $token = JWTAuth::getToken();
        $token = JWTAuth::refresh($token);
    }
    public function UploadString(Request $request)
    {
        return $content = base64_encode(file_get_contents($request->file('name')->getRealPath()));
        $document = Document::where('id',2)->first();
        event(new OrderShipped($document));
        return 0;
    }
    public function rename(Request $request)//rename
    {
        $array = array();
        $array['status'] = 200;
        $api = $request->header('Api-Token');
        $username = $this->userRepository->getNameByToken($api);

        $data = $request->input('data');
        $i = count($data)-1;
        for( $j = 0 ; $j <= $i ; $j++ ) { //get all request
            $uni_id[$j] = $data[$j]['uni_id'];
            $OriginalName[$j] = $this->fileRepository->GetFileNamebyUniid($uni_id[$j]);
            $ReName[$j] = $data[$j]['rename'];
        }
        for( $j = 0 ; $j <= $i ; $j++){ //check record in S3 before rename
            $hasfile = $this->fileRepository->GetFile($OriginalName[$j]);
            if ($hasfile=='[]')
                return response()->json(
                    [
                        'status' => 404,
                        'error' => [
                            'message' => $OriginalName[$j]." does not exist!"
                        ],
                    ]
                    , 400);
        }
        for( $j = 0 ; $j <= $i ; $j++ ){ //update S3
            $this->fileRepository->Rename($uni_id[$j],$ReName[$j],now(),$username);
        }
        for( $j = 0 ; $j <= $i ; $j++){ //check record in S3 after rename
            $hasfile = $this->fileRepository->GetFile($ReName[$j]);
            if($hasfile!=='[]')
                $array['data'][$j] = array($ReName[$j] => 'rename successfully!');
            else
                return response()->json(
                    [
                        'status' => 404,
                        'error' => $ReName[$j]." rename unsuccessfully!",
                    ]
                    , 400);
        }
        return $array;
    }
    public function delete(Request $request)
    {
        $array = array();
        $array['status'] = 200;
        $api = $request->header('Api-Token');
        $username = $this->userRepository->getNameByToken($api);
        $data = $request->input('data');

        $i = count($data)-1;
        for( $j = 0 ; $j <= $i ; $j++ ) {
            $uni_id = $data[$j]['uni_id'];
            $filename = $this->fileRepository->GetFileNamebyUniid($uni_id);
            $delete_files = $this->fileRepository->GetFile($filename);
            if ($delete_files=='[]') {
                return response()->json(
                    [
                        'status' => 404,
                        'error' => [
                            'message' => $filename . ' does not exist.'
                        ],
                    ]
                    ,400);
            } else {
                $this->fileRepository->UpdateName($uni_id,$filename, $username);
                $this->fileRepository->DeleteFile($uni_id,$filename);
                Storage::disk('s3')->delete($uni_id.'_'.$filename);

            }
            if ($this->fileRepository->GetFileOnlyTrashed($filename)) { // show softdelete objects
                $array['data'][$j] = array($filename  => 'delete successfully!');
            } else {
                $array['data'][$j] = array($filename  => 'delete unsuccessfully!');
            }
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

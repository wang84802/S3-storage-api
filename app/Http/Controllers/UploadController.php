<?php

namespace App\Http\Controllers;

use Redis;
use Exception;
use App\Examiners\Examiner;
use App\Services\UploadService;
use App\Http\Requests\UploadRequest;
use App\Repositories\TokenRepository;

class UploadController extends Controller
{
    public function __construct(TokenRepository $tokenRepository,UploadService $uploadService)
    {
        $this->tokenRepository = $tokenRepository;
        $this->uploadService = $uploadService;
    }

    public function Upload(UploadRequest $request)
    {
        try {
            Redis::connection('seq_db')->set('pool_status',1); //1 => busy
            $uni_id = uniqid();
            $servicename = $this->tokenRepository->GetServicebyToken($request->header('Api-Token'));
            $data = $request['data'];
            $filename = $data['filename'];
            $content = $data['content'];
            Examiner::Base64Invalid($content);

            $this->uploadService->Upload($uni_id,$filename,$servicename,$content);
            Redis::connection('seq_db')->set('pool_status',0); //0 => free
        }catch (Exception $e) {
            return [
                'status' => 403,
                'data' => [
                    'error' => [
                        'type' => get_class($e),
                        'code' => '400049106',
                        'message' => $e->getMessage()
                    ]
                ]
            ];
        }
        $array['status'] = 200;
        $array['data'] = array('status' => 'Upload '.$filename.' succeed.','uni_id' => $uni_id);
        return $array;
    }


}

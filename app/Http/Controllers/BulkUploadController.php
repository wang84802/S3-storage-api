<?php

namespace App\Http\Controllers;

use Redis;
use Exception;
use App\Examiners\Examiner;
use App\Services\UploadService;
use App\Http\Requests\UploadRequest;
use App\Http\Requests\BulkUploadRequest;
use App\Repositories\TokenRepository;
use App\Presenters\ResponsePresenter;

class BulkUploadController extends Controller
{
    public function __construct(TokenRepository $tokenRepository,UploadService $uploadService,ResponsePresenter $reponsePresenter)
    {
        $this->tokenRepository = $tokenRepository;
        $this->uploadService = $uploadService;
        $this->responsePresenter = $reponsePresenter;
    }

    public function BulkUpload(BulkUploadRequest $request)
    {
        try {
            Redis::connection('seq_db')->set('pool_status',1); //1 => busy
            $array = ['status' => 200, 'data' => array(), 'error' => array()];
            $servicename = $this->tokenRepository->GetServicebyToken($request->header('Api-Token'));

            for($i=0;$i<count($request->data);$i++)
            {
                $uni_id = uniqid();
                $filename = $request->data[$i]['filename'];
                $content = $request->data[$i]['content'];
                Examiner::Base64Invalid($content);
                $this->uploadService->Upload($uni_id,$filename,$servicename,$content);

                $input = array(
                    'status' => 200,
                    'status' => 'Upload '.$filename.' succeed.','uni_id' => $uni_id
                );
                array_push($array['data'], $input);
            }
            Redis::connection('seq_db')->set('pool_status',0); //0 => free
        }catch (Exception $e) {
            return response()->json([
                'status' => 403,
                'data' => [
                    'error' => [
                        'type' => get_class($e),
                        'code' => '400049107',
                        'message' => $e->getMessage()
                    ]
                ]
            ],400);
        }
        return $this->responsePresenter->Response($array);
    }


}

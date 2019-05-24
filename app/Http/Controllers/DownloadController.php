<?php

namespace App\Http\Controllers;

use Storage;
use Redis;
use App\Repositories\FileRepository;
use App\Services\DownloadService;
use App\Presenters\FilePresenter;
use App\Repositories\TokenRepository;
use App\Http\Requests\DownloadRequest;

class DownloadController extends Controller
{
    public function __construct(TokenRepository $tokenRepository,FileRepository $fileRepository,
        DownloadService $downloadService)
    {
        $this->tokenRepository = $tokenRepository;
        $this->fileRepository = $fileRepository;
        $this->downloadService = $downloadService;
    }

    /* Single Upload/Download  , Response with JSON */
    public function Download(DownloadRequest $request)// Download v1 (api/TaskDownload call TestDownload)
    {
        try {
            Redis::connection('seq_db')->set('pool_status',1); //1 => busy
            $uni_id = $request->data['uni_id'];
            $servicename = $this->tokenRepository->GetServicebyToken($request->header('Api-Token'));
            $filename = $this->fileRepository->GetFileNamebyUniid($uni_id);
            $this->downloadService->Download($uni_id,$filename,$servicename);
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
        $array['data'] = array('status' => 200,$filename => base64_encode(Storage::disk('local')->get('Download_Pool/'.$uni_id)));
        return $array;
    }
}

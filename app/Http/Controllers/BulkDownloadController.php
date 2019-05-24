<?php

namespace App\Http\Controllers;

use Redis;
use Storage;
use Exception;
use App\Examiners\Examiner;
use App\Services\DownloadService;
use App\Repositories\FileRepository;
use App\Repositories\TokenRepository;
use App\Presenters\ResponsePresenter;
use App\Http\Requests\BulkDownloadRequest;

class BulkDownloadController extends Controller
{
    public function __construct(TokenRepository $tokenRepository,FileRepository $fileRepository,
        ResponsePresenter $reponsePresenter,DownloadService $downloadService)
    {
        $this->tokenRepository = $tokenRepository;
        $this->fileRepository = $fileRepository;
        $this->responsePresenter = $reponsePresenter;
        $this->downloadService = $downloadService;
    }

    public function BulkDownload(BulkDownloadRequest $request)// Download v1 (api/TaskDownload call TestDownload)
    {
        try {

            Redis::connection('seq_db')->set('pool_status',1); //1 => busy
            $array = ['status' => 200, 'data' => array(), 'error' => array()];
            $servicename = $this->tokenRepository->GetServicebyToken($request->header('Api-Token'));

            for ($i = 0; $i < count($request->data); $i++) {
                $uni_id = $request->data[$i]['uni_id'];
                $filename = $this->fileRepository->GetFileNamebyUniid($uni_id);
                $this->downloadService->Download($uni_id, $filename, $servicename);

                $input = array(
                    'status' => 200,
                    $filename => base64_encode(Storage::disk('local')->get('Download_Pool/' . $uni_id))
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

<?php

namespace App\Http\Controllers;

use Storage;
use App\Jobs\TestUpload;
use App\Jobs\TestDownload;
use App\Examiners\Examiner;
use App\Http\Requests\UploadRequest;
use App\Http\Requests\DownloadRequest;
use App\Http\Requests\BulkDownloadRequest;
use App\Repositories\FileRepository;
use App\Repositories\TokenRepository;
use App\Repositories\StatusRepository;
use Illuminate\Contracts\Bus\Dispatcher;

class taskController extends Controller
{
    public function __construct(TokenRepository $tokenRepository,StatusRepository $statusRepository,FileRepository $fileRepository)
    {
        $this->tokenRepository = $tokenRepository;
        $this->statusRepository = $statusRepository;
        $this->fileRepository = $fileRepository;
    }

    /* Single Upload/Download  , Response with JSON */
    public function TestUpload(UploadRequest $request) // Upload v1 (api/TaskUpload call TestUpload)
    {
        Examiner::ServerBusy('Upload_Pool');
        $uni_id = uniqid();
        set_time_limit(0);
        $array['status'] = NULL;

        $servicename = $this->tokenRepository->GetServicebyToken($request->header('Api-Token'));
        $api_token = $request->header('Api-Token');
        $data = $request['data'];
        $filename =  $data['filename'];
        $content = $data['content'];
        Examiner::Base64Invalid($content);

        $this->statusRepository->Queue_processing();
        //Storage::disk('local')->put('Upload_Pool/'.$uni_id,base64_decode($content));
        $start = microtime(true);
        $timeout = false;
        $job = (new TestUpload($uni_id,$filename,$servicename,$api_token,$content));
        app(Dispatcher::class)->dispatch($job);

        do {
            $end = microtime(true);
            if($end-$start>5) {
                $timeout = true;
                break;
            }
        //} while(!($this->Storage_Exist('Upload_Pool/'.$uni_id)));
        } while(!($this->Storage_Exist('Upload_Pool/'.$uni_id) && ($this->fileRepository->Document_Exist($uni_id) != NULL)));

        $this->statusRepository->Queue_processed();
        if ($timeout == true) {
            $array['data'] = array('status' => 'Upload job failed.');
            $array['status'] = 500;
            return response()->json($array, 500);
        } else {
            $array['data'] = array('status' => 'Upload '.$filename.' succeed.','uni_id' => $uni_id);
            $array['status'] = 200;
            return $array;
        }

    }
    public function TestDownload(DownloadRequest $request)// Download v1 (api/TaskDownload call TestDownload)
    {
        Examiner::ServerBusy('Download_Pool');
        set_time_limit(0);
        $array['status'] = NULL;

        $uni_id = $request->data['uni_id'];
        $servicename = $this->tokenRepository->GetServicebyToken($request->header('Api-Token'));
        $filename = $this->fileRepository->GetFileNamebyUniid($uni_id);

        $this->statusRepository->Queue_processing();
//        $content = Storage::disk('s3')->get($uni_id);
//        Storage::disk('local')->put('Download_Pool/'.$uni_id,$content);
        $start = microtime(true);
        $timeout = false;
        $job = (new TestDownload($uni_id,$filename,$servicename));
        app(Dispatcher::class)->dispatch($job);

        do {
            $end = microtime(true);
            if($end-$start>5) {
                $timeout = true;
                break;
            }
        } while(!($this->Storage_Exist('Download_Pool/'.$uni_id)));

        $this->statusRepository->Queue_processed();
        if ($timeout == true) {
            $array['data'] = array($filename => 'Download job failed.');
            $array['status'] = 500;
            return response()->json($array, 500);
        } else {
            $array['data'] = array('status' => 'Download '.$filename.' succeed.'
                ,$filename => base64_encode(Storage::disk('local')->get('Download_Pool/'.$uni_id)));
            $array['status'] = 200;
            return $array;
        }
    }
    public function BulkDownload(BulkDownloadRequest $request)// Download v1 (api/TaskDownload call TestDownload)
    {
        Examiner::ServerBusy('Download_Pool');
        set_time_limit(0);
        $array['status'] = '500';
        $array['data'] = array();

        $servicename = $this->tokenRepository->GetServicebyToken($request->header('Api-Token'));
        $Data = $request->data;
        foreach($Data as $data) {
            $uni_id = $data['uni_id'];
            $filename = $this->fileRepository->GetFileNamebyUniid($uni_id);
            $this->statusRepository->Queue_processing();
            $start = microtime(true);
            $timeout = false;
            $job = (new TestDownload($uni_id,$filename,$servicename));
            app(Dispatcher::class)->dispatch($job);

            do {
                $end = microtime(true);
                if($end-$start>5) {
                    $timeout = true;
                    break;
                }
            } while(!($this->Storage_Exist('Download_Pool/'.$uni_id) && ($this->fileRepository->Document_Exist($uni_id) != NULL)));

            $this->statusRepository->Queue_processed();
            if ($timeout == true) {
                $input = array('status' => 'Download '.$filename.' failed.');
                array_push($array['data'],$input);
            } else {
                $input = array('status' => 'Download '.$filename.' succeed.'
                ,$filename => base64_encode(Storage::disk('local')->get('Download_Pool/'.$uni_id)));
                array_push($array['data'],$input);
                $array['status'] = 200;
            }
        }
        return $array;
    }

    public function Storage_Exist($file_path)
    {
        return Storage::disk('local')->exists($file_path);
    }
}

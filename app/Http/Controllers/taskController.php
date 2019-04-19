<?php

namespace App\Http\Controllers;

use Storage;
use App\Jobs\TestUpload;
use App\Jobs\TestDownload;
use App\Examiners\Examiner;
use App\Repositories\FileRepository;
use App\Repositories\TokenRepository;
use App\Repositories\StatusRepository;
use App\Http\Requests\UploadRequest;
use App\Http\Requests\DownloadRequest;
use App\Http\Requests\BulkDownloadRequest;
use App\Presenters\ResponsePresenter;
use Illuminate\Contracts\Bus\Dispatcher;

class taskController extends Controller
{
    public function __construct(TokenRepository $tokenRepository,StatusRepository $statusRepository,FileRepository $fileRepository,ResponsePresenter $reponsePresenter)
    {
        $this->tokenRepository = $tokenRepository;
        $this->statusRepository = $statusRepository;
        $this->fileRepository = $fileRepository;
        $this->responsePresenter = $reponsePresenter;
    }

    /* Single Upload/Download  , Response with JSON */
    public function TaskUpload(UploadRequest $request) // Upload v1 (api/TaskUpload call TestUpload)
    {
        Examiner::ServerBusy('Upload_Pool');
        $uni_id = uniqid();
        $servicename = $this->tokenRepository->GetServicebyToken($request->header('Api-Token'));
        $api_token = $request->header('Api-Token');
        $data = $request['data'];
        $filename =  $data['filename'];
        $content = $data['content'];
        Examiner::Base64Invalid($content);

        $this->statusRepository->Queue_processing();
        $start = microtime(true);
        $job = (new TestUpload($uni_id,$filename,$servicename,$api_token,$content));
        app(Dispatcher::class)->dispatch($job);

        $timeout = Examiner::JobCheck($start,$uni_id,'Upload_Pool');

        $this->statusRepository->Queue_processed();
        if ($timeout == true) {
            $array['status'] = 400;
            $array['error'] = array(['key' => 'Upload','code'=>'400049106','message'=>'The '.$filename.'upload failed.']);
            return response()->json($array, 400);
        } else {
            $array['status'] = 200;
            $array['data'] = array('status' => 'Upload '.$filename.' succeed.','uni_id' => $uni_id);
            return $array;
        }
    }

    public function TaskDownload(DownloadRequest $request)// Download v1 (api/TaskDownload call TestDownload)
    {
        Examiner::ServerBusy('Download_Pool');
        $uni_id = $request->data['uni_id'];
        $servicename = $this->tokenRepository->GetServicebyToken($request->header('Api-Token'));
        $filename = $this->fileRepository->GetFileNamebyUniid($uni_id);

        $this->statusRepository->Queue_processing();
        $start = microtime(true);
        $job = (new TestDownload($uni_id,$filename,$servicename));
        app(Dispatcher::class)->dispatch($job);

        $timeout = Examiner::JobCheck($start,$uni_id,'Download_Pool');

        $this->statusRepository->Queue_processed();
        if ($timeout == true) {
            $array['status'] = 400;
            $array['error'] = array(['key' => 'Download','code'=>'400049107','message'=>'The '.$uni_id.' download failed.']);
            return response()->json($array, 400);
        } else {
            $array['status'] = 200;
            $array['data'] = array($filename => base64_encode(Storage::disk('local')->get('Download_Pool/'.$uni_id)));
            return $array;
        }
    }
    public function BulkDownload(BulkDownloadRequest $request)// Download v1 (api/TaskDownload call TestDownload)
    {
        Examiner::ServerBusy('Download_Pool');
        set_time_limit(0);
        $array = ['status' => 400,'data' => array(),'error' => array()];
        $servicename = $this->tokenRepository->GetServicebyToken($request->header('Api-Token'));

        //foreach($Data as $data) {
        for($i=0;$i<count($request->data);$i++){
            $uni_id = $request->data[$i]['uni_id'];
            $a = Examiner::UniidInvalid($uni_id);
            if($a == false){
                $array['status'] = 400;
                $input = array('key' => 'data.'.$i.'.uni_id','code'=>'400049021','message'=>'The selected data.'.$i.'.uni_id is invalid.');
                array_push($array['error'],$input);
                continue;
            }
            $filename = $this->fileRepository->GetFileNamebyUniid($uni_id);
            $this->statusRepository->Queue_processing();
            $start = microtime(true);
            $job = (new TestDownload($uni_id,$filename,$servicename));
            app(Dispatcher::class)->dispatch($job);

            $timeout = Examiner::JobCheck($start,$uni_id,'Download_Pool');

            $this->statusRepository->Queue_processed();
            if ($timeout == true) {
                $array['status'] = 400;
                $input = array('key' => 'Download','code'=>'400049107','message'=>'The '.$uni_id.' download failed.');
                array_push($array['error'],$input);
            } else {
                $input = array('status' => 200,$filename => base64_encode(Storage::disk('local')->get('Download_Pool/'.$uni_id)));
                array_push($array['data'],$input);
            }
        }
        return $this->responsePresenter->Response($array);
    }
}

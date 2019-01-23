<?php

namespace App\Http\Controllers;

use Log;
use Storage;
use Validator;
use App\Document;
use Notification;
use App\Jobs\TestUpload;
use App\Jobs\TestDownload;
use Illuminate\Http\Request;
use App\Http\Requests\UploadRequest;
use App\Http\Requests\DownloadRequest;
use App\Repositories\FileRepository;
use App\Repositories\TokenRepository;
use App\Repositories\StatusRepository;
use App\Notifications\PoolNotification;
use Illuminate\Contracts\Bus\Dispatcher;

class taskController extends Controller
{
    /* Single Upload/Download  , Response with JSON */
    public function TestUpload(UploadRequest $request) // Upload v1 (api/TaskUpload call TestUpload)
    {
        $request->validated();
        $TokenRepository = new TokenRepository();
        $StatusRepository = new StatusRepository();
        $uni_id = uniqid();
        set_time_limit(0);
        $files = Storage::disk('local')->files('Upload_Pool');
        $files_with_size = 0;
        foreach ($files as $key => $file)
            if($this->Storage_Exist($file))
                $files_with_size += Storage::disk('local')->size($file);
        if($files_with_size>10) //256MB
            $this->Pool_Notify('Upload',$files_with_size,$uni_id);
        if($files_with_size>536870912) //512MB
            return response()->json(['status' => 500,'error' => ['message' => 'Server is Busy.']],500);

        $StatusRepository->Queue_processing();
        $servicename = $TokenRepository->GetServicebyToken($request->header('Api-Token'));
        $array = array();
        $array['status'] = NULL;
        $api_token = $request->header('Api-Token');
        $data = $request['data'];
        $filename =  $data['filename'];
        $content = $data['content'];


        Storage::disk('local')->put('Upload_Pool/'.$uni_id,base64_decode($content));
        $job = (new TestUpload($uni_id,$filename,$servicename,$api_token));
        app(Dispatcher::class)->dispatch($job);

        $StatusRepository->Queue_Processed();

        $array['data'] = array('status' => 'Upload '.$filename.' succeed.','uni_id' => $uni_id);
        $array['status'] = 200;
        return response()->json([$array],200);

    }
    public function TestDownload(DownloadRequest $request)// Download v1 (api/TaskDownload call TestDownload)
    {
        $request->validated();
        $StatusRepository = new StatusRepository();
        $FileRepostiory = new FileRepository();
        $TokenRepository = new TokenRepository();

        $files = Storage::disk('local')->files('Download_Pool');
        do{ // check storage area
            $files_with_size = 0;
            foreach ($files as $key => $file) {
                if($this->Storage_Exist($file))
                    $files_with_size += Storage::disk('local')->size($file);
            }
            if($files_with_size>268435456)
                $this->Pool_Notify('Download',$files_with_size);
            sleep(1);
        } while($files_with_size>536870912); //512MB

        $servicename = $TokenRepository->GetServicebyToken($request->header('Api-Token'));
        $uni_id = $request->data['uni_id'];
        set_time_limit(0);
        $array = array();
        $array['status'] = NULL;
        $filename = $FileRepostiory->GetFileNamebyUniid($uni_id);

        if($filename == NULL)
            return response()->json(
                [
                    'status' => 400,
                    'error' => [
                        'message' => 'The uni_id does not exist.'
                    ],
                ]
                ,400);
        $StatusRepository->Queue_processing();

        $start = microtime(true);
        $timeout = false;

        $job = (new TestDownload($uni_id,$filename,$servicename));
        app(Dispatcher::class)->dispatch($job);
        while (1)
        {
            $end = microtime(true);
            if($this->Storage_Exist('Download_Pool/'.$uni_id) && ($this->Document_Exist($uni_id) != NULL))
                break;
            else if($end-$start>5)
            {
                $timeout = true;
                break;
            }
        }
        if ($timeout == true && ($this->Document_Exist($uni_id) == NULL))
        {
            $StatusRepository->Queue_processed();
            $array['data'] = array($filename => 'Download job failed.');
            $array['status'] = 500;
            return response()->json([$array],500);
        }
        else
        {
            $StatusRepository->Queue_processed();
            return response()->json([
                'status' =>200,
                'data' => [
                    $filename => base64_encode(Storage::disk('local')->get('Download_Pool/'.$uni_id))
                ]
            ], 200);
        }
    }
    public function Document_Exist($uni_id)
    {
        return Document::where('uni_id',$uni_id)->first();
    }
    public function Storage_Exist($file_path)
    {
        return Storage::disk('local')->exists($file_path);
    }
    public function Pool_Notify($storage,$size,$uni_id)
    {
        Notification::route('slack', 'https://hooks.slack.com/services/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')->notify(new PoolNotification($storage,$size,$uni_id));

    }
}

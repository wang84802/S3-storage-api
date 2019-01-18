<?php

namespace App\Http\Controllers;

use DB;
use Log;
use Storage;
use App\File;
use App\Document;
use Validator;
use App\Jobs\upload;
use App\Jobs\TestUpload;
use App\Http\Requests\UploadRequest;
use App\Http\Requests\DownloadRequest;

use App\Repositories\FileRepository;
use App\Repositories\StatusRepository;
use App\Notifications\PoolNotification;
use Notification;

use App\Jobs\download;
use App\Jobs\TestDownload;
use Illuminate\Http\Request;
use Illuminate\Contracts\Bus\Dispatcher;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class taskController extends Controller
{
    /* Single Upload/Download  , Response with JSON */
    public function TestUpload(UploadRequest $request) // Upload v1 (api/TaskUpload call TestUpload)
    {
        //$username = 'wang123';
        $username = JWTAuth::parseToken()->authenticate();
        $username = $username->name;

        $validated = $request->validated();
        $StatusRepository = new StatusRepository();
        $FileRepostiory = new FileRepository();
        set_time_limit(0);
        $files = Storage::disk('local')->files('Upload_Pool');
        $files_with_size = 0;
        foreach ($files as $key => $file)
            if($this->Storage_Exist($file))
                $files_with_size += Storage::disk('local')->size($file);
        if($files_with_size>268435456) //268435456
            $this->Pool_Notify('Upload',$files_with_size);
        if($files_with_size>536870912) //512MB
            return response()->json(['status' => 503,'error' => ['message' => 'Server is Busy.']],500);

        $StatusRepository->Queue_processing();
        $array = array();

        $array['status'] = NULL;
        $api_token = $request->header('Api-Token');
        $start = microtime(true);
        $has_error = false;
        $timeout = false;
        $data = $request['data'];
        $filename =  $data['filename'];
        $uni_id = uniqid();
        $content = $data['content'];
        Storage::disk('local')->put('Upload_Pool/'.$uni_id,base64_decode($content));
        $job = (new TestUpload($uni_id,$filename,$username,$api_token));
        app(Dispatcher::class)->dispatch($job);

//        while (1)
//        {
//            $end = microtime(true);
//            if($this->Storage_Exist('Upload_Pool/'.$uni_id))
//                if($this->Document_Exist($uni_id)!=NULL)// successful
//                    break;
//            else if($end-$start>5)
//            {
//                $timeout = true;
//                break;
//            }
//        }
//        if ($timeout == true && ($this->Document_Exist($uni_id) == NULL))
//        {
//            $has_error = true;
//            $array['data'] = array($filename => 'Upload failed.');
//        }
//        else if($FileRepostiory->File_Exist($data['filename']))
//        {
//            $document = $this->Document_Exist($uni_id);
//            $array['data'] = array('status' => $document->file,'uni_id' => $uni_id);
//        }

        $StatusRepository->Queue_Processed();
//        if($has_error)
//        {
//            $array['status'] = 504;
//            return response()->json([$array],500);
//        }
//        else
        {
            $array['data'] = array('status' => $filename.' upload succeed.','uni_id' => $uni_id);
            $array['status'] = 200;
            return response()->json([$array],200);
        }
    }
    public function TestDownload(DownloadRequest $request)// Download v1 (api/TaskDownload call TestDownload)
    {
        $request->validated();
        $StatusRepository = new StatusRepository();
        $FileRepostiory = new FileRepository();

        set_time_limit(0);
        $array = array();
        $array['status'] = NULL;
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

        $uni_id = $request->data['uni_id'];

        $has_error = false;
        $name = $FileRepostiory->GetFileNamebyUniid($uni_id);

        if($name == NULL)
            return response()->json(
                [
                    'status' => 404,
                    'error' => [
                        'message' => 'File does not exist.'
                    ],
                ]
                ,400);
        $StatusRepository->Queue_processing();

        $start = microtime(true);
        $timeout = false;

        $job = (new TestDownload($uni_id,$name));
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
            $has_error = true;
            $array['data'] = array($name => 'Download job failed.');
        }
        else
        {
            $document = $this->Document_Exist($uni_id);
            $array['data'] = array($name => $document['file']);
        }
        if($has_error == true)
        {
            $StatusRepository->Queue_processed();
            $array['status'] = 504;
            return response()->json([$array],500);
        }
        else
        {
            $StatusRepository->Queue_processed();
            return response()->json([
                'status' =>200,
                'data' => [
                    $name => base64_encode(Storage::disk('local')->get('Download_Pool/'.$uni_id))
                ]
            ], 200);
        }
    }

    public function task_upload(Request $request)
    {
        $start = microtime(true);
        $timeout = false;
        $api_token = $request->header('Api-Token');
        $request = $request->all();
        $job = (new upload($request,$api_token));//upload
        $id = app(Dispatcher::class)->dispatch($job);
        while (1)
        {
            $end = microtime(true);
            if($this->Document_Exist($id))
                break;
            else if($end-$start>20)
            {
                $timeout = true;
                break;
            }else
                sleep(1);
        }
        if ($timeout == true)
            abort(408,'Upload job failed.');
        else
        {
            $document = $this->Document_Exist($id);
            return $document->file;
        }
    }
    public function task_download(Request $request)
    {
        $start = microtime(true);
        $request = $request->all();
        $name = $request['data']['filename'];
        $result = $this->File_Exist($name);
        $timeout = false;
        if($result=='[]')
            abort(404, $name.' does not exist.'); //not exist in db
        else {
            $job = (new download($request));
            $id = app(Dispatcher::class)->dispatch($job);
            while (1)
            {
                $end = microtime(true);
                if($this->Document_Exist($id))
                    break;
                else if($end-$start>10)
                {
                    $timeout = true;
                    break;
                }
                else
                    sleep(1);
            }
            if ($timeout == true)
                abort(408,'Download job failed.');
            else
            {
                $document = $this->Document_Exist($id);
                return $document->file;
            }
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
    public function Pool_Notify($storage,$size)
    {
        Notification::route('slack', 'https://hooks.slack.com/services/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')->notify(new PoolNotification($storage,$size));

    }
}

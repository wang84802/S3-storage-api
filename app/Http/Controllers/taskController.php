<?php

namespace App\Http\Controllers;

use App\File;
use App\Document;
use App\Jobs\upload;
use App\Jobs\download;
use App\Jobs\test_upload;
use App\Jobs\test_download;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Bus\Dispatcher;

class taskController extends Controller
{
///* * * Multiple Upload/Download * * *///
    public function TestUpload(Request $request)
    {
        $array = array();
        $api_token = $request->header('Api-Token');
        $count = count($request->data)-1;
        for ($i=0;$i<=$count;$i++)
        {
            $start = microtime(true);
            $timeout = false;
            $data = $request['data'][$i];
            $job = (new test_upload($data,$api_token));
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
                }else
                    sleep(1);
            }
            if ($timeout == true)
                $array['response'][$i] = array($id => 'Upload job failed.');
            else
            {
                if(File::where('name',$data['filename'])->where('extension',$data['extension'])->exists())
                    $array['response'][$i] = array($id => 'Upload job succeed.');
            }
        }
        return json_encode($array);
    }
    public function TestDownload(Request $request)
    {
        $array = array();
        $data = $request->data;
        $count = count($data)-1;
        for($i=0;$i<=$count;$i++)
        {
            $name = $data[$i]['filename'];
            $result = $this->File_Exist($name);
            if($result=='[]')
                abort(404, $name.' does not exist.'); //not exist in db
            else
            {
                $start = microtime(true);
                $timeout = false;
                $job = (new test_download($name));
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
                    }else
                        sleep(1);
                }
                if ($timeout == true)
                    $array['response'][$i] = array($id => 'Upload job failed.');
                else
                {
                    $document = $this->Document_Exist($id);
                    $array['response'][$i] = array($id => $document->file);
                }
            }
        }
        return json_encode($array);
    }
///* * * Single Upload/Download * * *///
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
            else if($end-$start>10)
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
    public function Document_Exist($id)
    {
        return Document::where('job_id',$id)->first();
    }
    public function File_Exist($name)
    {
        return File::where(
                'name',strstr($name,strrchr($name,'.'),true)) //get original file name
            ->get();
    }
}

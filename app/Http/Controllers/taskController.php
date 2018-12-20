<?php

namespace App\Http\Controllers;

use function GuzzleHttp\Psr7\_caseless_remove;
use Storage;
use App\File;
use App\Document;
use App\Jobs\upload;
use App\Jobs\download;
use App\Jobs\test_upload;
use App\Jobs\test_download;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Bus\Dispatcher;
use Log;

class taskController extends Controller
{
    /* Multiple Upload/Download with JSON */
    public function TestUpload(Request $request)
    {
        $start_total = microtime(true);
        $files = Storage::disk('local')->files('Upload_Pool');

        $files_with_size = 0;
        foreach ($files as $key => $file)
            if($this->Storage_Exist($file))
                $files_with_size += Storage::disk('local')->size($file);
        if($files_with_size>536870912)
            return response('Server is Busy.');

        $array = array();
        $api_token = $request->header('Api-Token');
        $count = count($request->data)-1;
        for ($i=0;$i<=$count;$i++)
        {
            $start = microtime(true);
            $timeout = false;
            $data = $request['data'][$i];
            $job = (new test_upload($data,$api_token));
            $FilewithExtension =  $data['filename'].'.'.$data['extension'];
            $id = app(Dispatcher::class)->dispatch($job);
            while (1)
            {
                $end = microtime(true);
                if($this->Storage_Exist('Upload_Pool/'.$id.'_'.$FilewithExtension))
                    if($this->Document_Exist($id)!==NULL)
                        break;
                else if($end-$start>15)
                {
                    $timeout = true;
                    break;
                }else
                    sleep(1);
            }
            if ($timeout == true)
                $array['response'][$i] = array($FilewithExtension => 'Upload job timeout.');
            else if(File::where('name',$data['filename'])->where('extension',$data['extension'])->exists())
            {
                $document = $this->Document_Exist($id);
                $array['response'][$i] = array($FilewithExtension => $document->file);
            }

        }
        $end_total = microtime(true);
        Log::info("start:".$start_total);
        Log::info("end  :".$end_total);
        Log::info('Upload total time:'.($end_total-$start_total));
        return ($array);
    }
    public function TestDownload(Request $request)
    {
        $array = array();
        $files = Storage::disk('local')->files('Download_Pool');
        do{ // check storage area
            $files_with_size = 0;
            foreach ($files as $key => $file) {
                if($this->Storage_Exist($file))
                    $files_with_size += Storage::disk('local')->size($file);
            }
            sleep(1);
        } while($files_with_size>536870912);

        $data = $request->data;
        $zipfile = $request->zipfile;
        if($zipfile == NULL)
            return response('Zip file name is required.',400);
        if($this->Storage_Exist('Download_Pool/'.$zipfile))
            return response($zipfile.' already exist.',409);

        $count = count($data)-1;
        for($i=0;$i<=$count;$i++)
            if($this->File_Exist($data[$i]['filename'])=='[]')
                response($data[$i]['filename'].' does not exist.',404); //not exist in db
        $has_error = false;
        for($i=0;$i<=$count;$i++)
        {
            $name = $data[$i]['filename'];
            $start = microtime(true);
            $timeout = false;
            $job = (new test_download($name,$zipfile));
            $id = app(Dispatcher::class)->dispatch($job);
            while (1)
            {
                $end = microtime(true);
                if($this->Storage_Exist('Download_Pool/'.$id.'_'.$name) || $this->Document_Exist($id) != NULL)
                    break;
                else if($end-$start>20)
                {
                    $timeout = true;
                    break;
                }else
                    sleep(1);
            }
            if ($timeout == true)
            {
                $has_error = true;
                $array['response'][$i] = array($name => 'Download job timeout.');
            }
            else
            {
                $document = $this->Document_Exist($id);
                $array['response'][$i] = array($name => $document->file);
            }
        }
        if($has_error == true)
            return $array;
        else
        {
            $zipfile_content = Storage::disk('local')->get('Download_Pool/'.$zipfile);
            return array($zipfile => base64_encode($zipfile_content));
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
    public function Storage_Exist($file_path)
    {
        return Storage::disk('local')->exists($file_path);
    }
}

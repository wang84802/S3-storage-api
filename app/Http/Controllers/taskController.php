<?php

namespace App\Http\Controllers;

use App\File;
use App\Document;
use App\Jobs\upload;
use App\Jobs\download;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Bus\Dispatcher;
use App\Repositories\UserRepository;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use App\Events\OrderShipped;

class taskController extends Controller
{
    public function task_upload(Request $request)
    {
        $start = microtime(true);
        $timeout = false;
        $api = $request->header('Api-Token');
        $request = $request->all();
        $job = (new upload($request,$api));//upload
        $id = app(Dispatcher::class)->dispatch($job);
        while (1)
        {
            $end = microtime(true);
            if(Document::where('job_id',$id)->first())
                break;
            else if($end-$start>15)
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
            $document = Document::where('job_id',$id)->first();
            return $document->file;
        }
    }
    public function task_download(Request $request)
    {
        $start = microtime(true);
        $request = $request->all();
        $name = $request['data']['filename'];
        $result = File::where('name',strstr($name,'.',-1))->get(); //not exist in db
        $timeout = false;
        if($result=='[]')
            abort(404, $name.' does not exist.'); //not exist in db
        else {
            $job = (new download($request));
            $id = app(Dispatcher::class)->dispatch($job);
            while (1)
            {
                $end = microtime(true);
                if(Document::where('job_id',$id)->first())
                    break;
                else if($end-$start>15)
                {
                    $timeout = true;
                    break;
                }else
                    sleep(1);
            }
            if ($timeout == true)
                abort(408,'Download job failed.');
            else
            {
                $document = Document::where('job_id',$id)->first();
                return $document->file;
            }
        }
    }
}

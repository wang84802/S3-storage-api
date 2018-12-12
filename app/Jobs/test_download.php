<?php

namespace App\Jobs;

use App\User;
use App\File;
use App\Document;
use App\Jobs\Job;
use Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\JobFailed;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class test_download implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
    public function handle()
    {
        $id = $this->job->getJobId();
        $filename = $this->name;
        if($this->Exist_S3($filename) && $this->Exist_File($filename))
        {
            $content = $this->Get_Content($filename);
            $this->Create_Document($id,base64_encode($content));
        }
        else
            $this->Create_Document($id,'File does not exist!');
    }
    public function Exist_S3($filename)
    {
        return Storage::disk('s3')->exists($filename);
    }
    public function Get_Content($filename)
    {
        return Storage::disk('s3')->get($filename);
    }
    public function Create_Document($id,$content)
    {
        Document::create([
            'job_id' => $id,
            'file' => $content,
        ]);
    }
    public function Exist_Document($id)
    {
        return Document::where('job_id',$id)->exists();
    }
    public function Exist_File($name)
    {
        return File::where(
            'name',strstr($name,strrchr($name,'.'),true)) //get original file name
        ->exists();
    }
}

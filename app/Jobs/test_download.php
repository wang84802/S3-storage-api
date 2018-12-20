<?php

namespace App\Jobs;

use App\File;
use App\Document;
use App\Jobs\Job;
use Storage;
use Zipper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\JobFailed;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class test_download implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $name,$zipfile;

    public function __construct($name,$zipfile)
    {
        $this->name = $name;
        $this->zipfile = $zipfile;
    }
    public function handle()
    {
        $id = $this->job->getJobId();
        $filename = $this->name;
        $zipfile = $this->zipfile;
        if($this->Exist_S3($filename) && $this->Exist_File($filename))
        {
            $content = $this->Get_Content($filename);
            Storage::disk('local')->put('Download_Pool/'.$id.'_'.$filename,$content);
            $start = microtime(true);
            $zip = new \ZipArchive();
            $this->Zip_Local($zip,$zipfile,$filename,$id);
            $this->Create_Document($id,'Download succeed.');
            $end = microtime(true);
            Log::info('One file download time:'.($end-$start));
        }
        else
            $this->Create_Document($id,'File does not exist!');
    }

    private function Exist_S3($filename)
    {
        return Storage::disk('s3')->exists($filename);
    }
    private function Exist_File($name)
    {
        return File::where(
            'name',strstr($name,strrchr($name,'.'),true)) //get original file name
        ->exists();
    }
    private function Get_Content($filename)
    {
        return Storage::disk('s3')->get($filename);
    }
    private function Zip_Local($zip,$zipfile,$filename,$id)
    {
        $zip->open('/var/www/html/S3/storage/app/Download_Pool/'.$zipfile,\ZipArchive::CREATE);
        $zip->addFromString($filename,file_get_contents('/var/www/html/S3/storage/app/Download_Pool/'.$id.'_'.$filename));
        $zip->close();
    }
    private function Create_Document($id,$content)
    {
        Document::create([
            'job_id' => $id,
            'file' => $content,
        ]);
    }
    private function Exist_Document($id)
    {
        return Document::where('job_id',$id)->exists();
    }

}

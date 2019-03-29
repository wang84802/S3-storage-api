<?php

namespace App\Jobs;

use Log;
use Storage;
use App\Document;
use App\Jobs\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\JobFailed;
use App\Repositories\SeqRepository;
use App\Repositories\FileRepository;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TestDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $name,$uni_id,$servicename;

    public function __construct($uni_id,$name,$servicename)
    {
        $this->uni_id = $uni_id;
        $this->name = $name;
        $this->servicename = $servicename;
    }
    public function handle()
    {
        $FileRepository = new FileRepository();
        $SeqRepository = new SeqRepository();

        $filename = $this->name;
        $servicename = $this->servicename;
        $document_seq_id = $SeqRepository->Generate_seq(2); //Document ID
        Log::info('Download');
        if($this->Exist_S3($this->uni_id))
        {
            Log::info("download test");
            $content = $this->Get_Content($this->uni_id); //get file from S3
            Log::info(base64_encode($content));
            Storage::disk('local')->put('Download_Pool/'.$this->uni_id,$content); // put into Download pool
        }
        if($this->Exist_Local($this->uni_id))
            $FileRepository->Create_Document($document_seq_id,$this->uni_id,'UAT Download '.$filename.' succeed.',$servicename);
        else
            $FileRepository->Create_Document($document_seq_id,$this->uni_id,'File does not exist!',$servicename);



    }
    private function Exist_Local($uni_id)
    {
        return Storage::disk('local')->exists('Download_Pool/'.$uni_id);
    }
    private function Exist_S3($filename)
    {
        return Storage::disk('s3')->exists($filename);
    }
    private function Get_Content($filename)
    {
        return Storage::disk('s3')->get($filename);
    }
}

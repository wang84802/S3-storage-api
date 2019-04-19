<?php

namespace App\Jobs;

use Log;
use Storage;
use App\Presenters\FilePresenter;
use App\Repositories\SeqRepository;
use App\Repositories\FileRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class TestUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $uni_id,$filename,$servicename,$api_token,$content;

    public function __construct($uni_id,$filename,$servicename,$api_token,$content)
    {
        $this->uni_id = $uni_id;
        $this->filename = $filename;
        $this->servicename = $servicename;
        $this->api_token = $api_token;
        $this->content = $content;
    }

    public function handle()
    {
        Log::info('upload test');
        $FilePresenter = new FilePresenter();
        $SeqRepository = new SeqRepository();
        $FileRepository = new FileRepository();

        $file_seq_id = $SeqRepository->Generate_seq(1); //File ID
        $document_seq_id = $SeqRepository->Generate_seq(2); //Document ID
        $servicename = $this->servicename;
        $FileName = $this->filename;

        Storage::disk('local')->put('Upload_Pool/'.$this->uni_id,base64_decode($this->content));
        $content_local = Storage::disk('local')->get('Upload_Pool/'.$this->uni_id);//get from pool
        $this->Upload_S3($this->uni_id, $content_local);//put in S3
        $size = $FilePresenter->getsize($this->Get_Size($this->uni_id));//get size from S3

        if($this->Exist_Local($this->uni_id)) {
            $FileRepository->Create_Document($document_seq_id,$this->uni_id,'UAT Upload '.$FileName.' succeed.',$servicename);
            $FileRepository->Create_File($file_seq_id,$this->uni_id,$FileName,$size,$servicename,$servicename);
        } else
            $FileRepository->Create_Document($document_seq_id,$this->uni_id,'Upload failed!',$servicename);
    }
    private function Exist_Local($uni_id)
    {
        return Storage::disk('local')->exists('Upload_Pool/'.$uni_id);
    }
    public function Upload_S3($filename,$content)
    {
        Storage::disk('s3')->put($filename,$content);
    }
    public function Get_Size($filename)
    {
        return Storage::disk('s3')->size($filename);
    }
}

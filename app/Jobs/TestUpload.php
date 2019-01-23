<?php

namespace App\Jobs;
use App\User;
use App\File;
use App\Document;
use Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Presenters\FilePresenter;
use App\Repositories\SeqRepository;
use App\Repositories\FileRepository;
use Tymon\JWTAuth\Exceptions\JWTException;

class TestUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $filename;
    public $uni_id;
    public $api_token;
    public $servicename;

    public function __construct($uni_id,$filename,$servicename,$api_token)
    {
        $this->uni_id = $uni_id;
        $this->filename = $filename;
        $this->servicename = $servicename;
        $this->api_token = $api_token;
    }

    public function handle()
    {
        $FilePresenter = new FilePresenter();
        $SeqRepository = new SeqRepository();
        $FileRepository = new FileRepository();
        
        $file_seq_id = $SeqRepository->Generate_seq('select','currval_storage(1,1)'); //File ID
        $document_seq_id = $SeqRepository->Generate_seq('select','currval_storage(2,1)'); //Document ID
        $servicename = $this->servicename;
        $FileName = $this->filename;

        $content_local = Storage::disk('local')->get('Upload_Pool/'.$this->uni_id);//get from pool
        $this->Upload_S3($this->uni_id, $content_local);//put in S3
        $size = $FilePresenter->getsize($this->Get_Size($this->uni_id));//get size from S3

        $FileRepository->Create_File($file_seq_id,$this->uni_id,$FileName,$size,$servicename);
        $FileRepository->Create_Document($document_seq_id,$this->uni_id,'Upload '.$FileName.' succeed.',$servicename);
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

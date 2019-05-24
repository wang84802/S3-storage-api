<?php

namespace App\Services;

use Storage;
use App\Presenters\FilePresenter;
use App\Repositories\SeqRepository;
use App\Repositories\FileRepository;

class UploadService
{
    public function Upload($uni_id,$filename,$servicename,$content)
    {
        $FilePresenter = new FilePresenter();
        $SeqRepository = new SeqRepository();
        $FileRepository = new FileRepository();

        $file_seq_id = $SeqRepository->Generate_seq(1); //File ID
        $document_seq_id = $SeqRepository->Generate_seq(2); //Document ID
        $servicename = $servicename;
        $FileName = $filename;

        Storage::disk('local')->put('Upload_Pool/'.$uni_id,base64_decode($content)); //put into Upload_Pool
        $content_local = Storage::disk('local')->get('Upload_Pool/'.$uni_id);//get from pool

        $this->Upload_S3($uni_id, $content_local);//put in S3
        $size = $FilePresenter->getsize($this->Get_Size($uni_id));//get size from S3

        $FileRepository->Create_Document($document_seq_id,$uni_id,'Uat Upload '.$FileName.' succeed.',$servicename);
        $FileRepository->Create_File($file_seq_id,$uni_id,$FileName,$size,$servicename,$servicename);
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

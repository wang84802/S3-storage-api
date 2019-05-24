<?php

namespace App\Services;

use Log;
use Storage;
use App\Repositories\SeqRepository;
use App\Repositories\FileRepository;


class DownloadService
{
    public function Download($uni_id,$filename,$servicename)
    {
        $SeqRepository = new SeqRepository();
        $fileRepository = new FileRepository();

        $document_seq_id = $SeqRepository->Generate_seq(2); //Document ID
        if($this->Exist_S3($uni_id))
        {
            $content = $this->Get_Content($uni_id); //get file from S3
            Storage::disk('local')->put('Download_Pool/'.$uni_id,$content); // put into Download pool
        }
        //if($this->Exist_Local($uni_id))
            $fileRepository->Create_Document($document_seq_id,$uni_id,'Uat Download '.$filename.' succeed.',$servicename);
    }
    private function Exist_S3($filename)
    {
        return Storage::disk('s3')->exists($filename);
    }
    private function Exist_Local($uni_id)
    {
        return Storage::disk('local')->exists('Download_Pool/'.$uni_id);
    }
    private function Get_Content($filename)
    {
        return Storage::disk('s3')->get($filename);
    }
}

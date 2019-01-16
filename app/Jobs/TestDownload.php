<?php

namespace App\Jobs;

use App\File;
use App\Document;
use App\Jobs\Job;
use Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\JobFailed;
use App\Repositories\SeqRepository;
use App\Repositories\FileRepository;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class TestDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $name;
    public $uni_id;
    public function __construct($uni_id,$name)
    {
        $this->uni_id = $uni_id;
        $this->name = $name;
    }
    public function handle()
    {
        $filename = $this->name;

        $SeqRepository = new SeqRepository();
        $seq_id = $SeqRepository->Generate_seq('select','currval_storage(1,1)');

        $FileRepository = new FileRepository();
        if($this->Exist_S3($this->uni_id) && $FileRepository->File_Exist($filename))
        {
            $content = $this->Get_Content($this->uni_id);
            Storage::disk('local')->put('Download_Pool/'.$this->uni_id,$content);
            $this->Create_Document($seq_id,$this->uni_id,'Download succeed.');
        }
        else
            $this->Create_Document($seq_id,$this->uni_id,'File does not exist!');
    }

    private function Exist_S3($filename)
    {
        return Storage::disk('s3')->exists($filename);
    }
    private function Get_Content($filename)
    {
        return Storage::disk('s3')->get($filename);
    }
    private function Create_Document($seq_id,$uni_id,$content)
    {
        Document::create([
            'id' => $seq_id,
            'uni_id' => $uni_id,
            'file' => $content,
        ]);
    }

}

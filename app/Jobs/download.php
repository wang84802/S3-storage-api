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

class download implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $body;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($body)
    {
        $this->body = $body;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $id = $this->job->getJobId();

        $filename = $this->body['data']['filename'];
        if($this->Exist_S3($filename))
        {
            $content = $this->Get_Content($filename);
            $this->Create_Document($id,base64_encode($content));
        }
        else
            $this->Create_Document($id,'File does not exist!');
        if(!$this->Exist_DB($id)) //not create db
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
    public function Exist_DB($id)
    {
        return Document::where('job_id',$id)->exists();
    }
}

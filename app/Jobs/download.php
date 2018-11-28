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
        if(!Storage::disk('s3')->exists($filename))
            $this->failed();
        $content =  Storage::disk('s3')->get($filename);
        Document::create([
            'job_id' => $id,
            'file' => base64_encode($content),
        ]);
        if(Document::where('job_id',$id)->doesntExist()) //not exist in S3
            $this->failed();
    }
    public function failed()
    {
        $id = $this->job->getJobId();
        Document::create([
            'job_id' => $id,
            'file' => 'File does not exist!',
        ]);
    }
}

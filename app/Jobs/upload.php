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

class upload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $request;
    public $api;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request,$api)
    {
        $this->request = $request;
        $this->api = $api;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $name = $this->request['data'];
        $username = User::where('api_token',$this->api)->value('name');//get username by token

        $i = count($name) - 1;
        for ($j = 0; $j <= $i; $j++) {

            $FileName[$j] = $name[$j]['filename'];

            File::where('name',$FileName[$j])->delete(); //soft-delete

            $Extension[$j] = strtolower(($name[$j]['extension']));
            $FileWithExtension[$j] = $FileName[$j] . '.' . $Extension[$j];
            $content[$j] = $name[$j]['content'];

            Storage::disk('s3')->put($FileWithExtension[$j], base64_decode($content[$j])); //upload

            $size[$j] = Storage::disk('s3')->size($FileWithExtension[$j]); //get file size

            File::create([
                'name' => $FileName[$j],
                'extension' => strtolower($Extension[$j]),
                'size' => $this->getsize($size[$j]),
                'created_by' => $username,
                'updated_by' => $username,
            ]);//create file
        }
        $id = $this->job->getJobId();
        Document::create([
            'job_id' => $id,
            'file' => 'Upload job done',
        ]);
    }
    public function getsize($size)
    {
        $unit = 'B';
        $divide_time = 0;
        while($size/1024 >= 1)
        {
            $size=$size/1024;
            $divide_time++;
        }
        if($divide_time==1)
            $unit = 'KB';
        else if($divide_time==2)
            $unit = 'MB';
        else if($divide_time==3)
            $unit = 'GB';
        else if($divide_time==4)
            $unit = 'TB';
        $size = number_format($size,1,'.','').' '.$unit;
        return $size;
    }

}

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
use Log;

class test_upload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $data;
    public $api_token;

    public function __construct($data,$api_token)
    {
        $this->data = $data;
        $this->api_token = $api_token;
    }

    public function handle()
    {
        $id = $this->job->getJobId();
        $username = $this->Get_UserName($this->api_token);//get username by token

        $FileName = $this->data['filename'];
        $Extension = strtolower(($this->data['extension']));
        $FileWithExtension = $FileName . '.' . $Extension;
        $content = $this->data['content'];
        $start = microtime(true);
        //Log::info('Put in pool start time:'.microtime(true));
        Storage::disk('local')->put('Upload_Pool/'.$id.'_'.$FileWithExtension, base64_decode($content));
        $content = Storage::disk('local')->get('Upload_Pool/'.$id.'_'.$FileWithExtension);
        //Log::info('Upload to S3 start time:'.microtime(true));
        $this->Upload_S3($FileWithExtension, $content);
        $end = microtime(true);
        //Log::info('Upload end time:'.($end));
        //Log::info('One file upload time:'.($end-$start));
        $size = $this->Get_Size($FileWithExtension);

        $result = $this->Check_File($FileName,$Extension);
        $FilePresenter = new FilePresenter();
        if($result->exists())
            $result->update(['updated_by' => $username,'size' => $FilePresenter->getsize($size)]);
        else
            $this->Create_File($FileName,$Extension,$size,$username);

        $this->Create_Document($id);
    }
    public function Get_UserName($api_token)
    {
        return User::where('api_token',$api_token)->value('name');
    }
    public function Upload_S3($filename,$content)
    {
        Storage::disk('s3')->put($filename,$content);
    }
    public function Get_Size($filewithextension)
    {
        return Storage::disk('s3')->size($filewithextension);
    }
    public function Check_File($filename,$extension)
    {
        return File::where('name',$filename)->where('extension',$extension);
    }
    public function Create_File($name,$extension,$size,$username)
    {
        $FilePresenter = new FilePresenter();
        File::create([
            'name' => $name,
            'extension' => strtolower($extension),
            'size' => $FilePresenter->getsize($size),
            'created_by' => $username,
            'updated_by' => $username,
        ]);
    }
    public function Create_Document($id)
    {
        Document::create([
            'job_id' => $id,
            'file' => 'Upload succeed.',
        ]);
    }
}

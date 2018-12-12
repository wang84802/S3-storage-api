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
        $username = $this->Get_UserName($this->api_token);//get username by token

        //$FileName = $this->data['filename'];
        $Extension = strtolower(($this->data['extension']));
        $FileWithExtension = $FileName . '.' . $Extension;
        $content = $this->data['content'];

        $this->Upload_S3($FileWithExtension, base64_decode($content));
        $size = $this->Get_Size($FileWithExtension);

        $result = $this->Check_File($FileName,$Extension);

        if($result->exists())
            $result->update(['updated_by' => $username,'size' => $this->Size_with_Unit($size)]);
        else
            $this->Create_File($FileName,$Extension,$size,$username);

        $this->Create_Document($FileWithExtension);
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
        File::create([
            'name' => $name,
            'extension' => strtolower($extension),
            'size' => $this->Size_with_Unit($size),
            'created_by' => $username,
            'updated_by' => $username,
        ]);
    }
    public function Create_Document($FileWithExtension)
    {
        $id = $this->job->getJobId();
        Document::create([
            'job_id' => $id,
            'file' => $FileWithExtension.' upload succeed.',
        ]);
    }
    public function Size_with_Unit($size)
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
        $size = number_format($size,1).' '.$unit; //first decimal place
        return $size;
    }
}

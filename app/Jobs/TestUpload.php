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
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Log;

class TestUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $data;
    public $uni_id;
    public $api_token;
    public $username;

    public function __construct($uni_id,$data,$username,$api_token)
    {
        $this->uni_id = $uni_id;
        $this->data = $data;
        $this->username = $username;
        $this->api_token = $api_token;
    }

    public function handle()
    {
        $FilePresenter = new FilePresenter();
        $SeqRepository = new SeqRepository();
        $seq_id = $SeqRepository->Generate_seq('select','currval_storage(1,1)');

        $username = $this->username;

        $FileName = $this->data['filename'];
        $content = $this->data['content'];
        Storage::disk('local')->put('Upload_Pool/'.$this->uni_id, base64_decode($content));//put in pool
        $content_local = Storage::disk('local')->get('Upload_Pool/'.$this->uni_id);//get from pool

        $this->Upload_S3($this->uni_id, $content_local);//put in S3
        $size = $FilePresenter->getsize($this->Get_Size($this->uni_id));//get size from S3

        $this->Create_File($seq_id,$this->uni_id,$FileName,$size,$username);
        $this->Create_Document($seq_id,$this->uni_id,$FileName,$username);
    }
    public function Get_UserName($api_token)
    {
        return User::where('api_token',$api_token)->value('name');
    }
    public function Upload_S3($filename,$content)
    {
        Storage::disk('s3')->put($filename,$content);
    }
    public function Get_Size($filename)
    {
        return Storage::disk('s3')->size($filename);
    }
    public function Create_File($seq_id,$uni_id,$name,$size,$username)
    {
        File::create([
            'id' => $seq_id,
            'uni_id' => $uni_id,
            'name' => $name,
            'size' => $size,
            'created_by' => $username,
            'updated_by' => $username,
        ]);
    }
    public function Create_Document($seq_id,$uni_id,$FileName,$username)
    {
        Document::create([
            'id' => $seq_id,
            'uni_id' => $uni_id,
            'file' => $FileName.' upload succeed.',
            'created_by' => $username,
        ]);
    }
}

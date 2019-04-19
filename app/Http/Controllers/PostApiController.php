<?php

namespace App\Http\Controllers;

use DB;
use Storage;
use GuzzleHttp\Client;
use App\Examiners\Examiner;
use Illuminate\Http\Request;
use App\Repositories\SeqRepository;
use App\Repositories\UserRepository;
use App\Repositories\FileRepository;
use App\Repositories\TokenRepository;
use App\Http\Requests\RenameRequest;
use App\Http\Requests\DownloadRequest;
use App\Http\Requests\BulkDeleteRequest;
use App\Http\Requests\BulkDownloadRequest;

class PostApiController extends Controller
{
    public function __construct(TokenRepository $TokenRepository,FileRepository $fileRepository,SeqRepository $SeqRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->TokenRepository = $TokenRepository;
        $this->SeqRepository = $SeqRepository;
    }
    public function test(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'http://localhost/hello', [

        ]);
        $response = $response->getBody()->getContents();
        
        print_r($response);

        //$request = new Request('GET', 'http://httpbin.org/get');

// You can provide other optional constructor arguments.
//        $headers = ['X-Foo' => 'Bar'];
//        $body = 'hello!';
//        $request = new Request('PUT', 'http://httpbin.org/put', $headers, $body);
//        return $request;

//        $client = new \GuzzleHttp\Client();
//
//        $promise1 = $client->getAsync('http://192.167.56.1/rm_label_test')->then(
//            function ($response) {
//                return $response->getBody();
//            }, function ($exception) {
//                return $exception->getMessage();
//            }
//        );

//
//        $promise2 = $client->getAsync('http://loripsum.net/api')->then(
//            function ($response) {
//                return $response->getBody();
//            }, function ($exception) {
//                return $exception->getMessage();
//            }
//        );
//
//        $response1 = $promise1->wait();
//        $response2 = $promise2->wait();
//
//        echo $response1;
//        echo $response2;

//        $client = new \GuzzleHttp\Client();
//        $request = new \GuzzleHttp\Psr7\Request('GET', 'http://1192.168.19.126/rm_label_test');
//        $promise = $client->sendAsync($request)->then(function ($response)
//            {
//                echo 'I completed! ' . $response->getBody();
//            }
//        );
//        return $promise->wait();
//
//        $num = 1367;
//        $sum = 0;
//
//        return $this->resursive($sum,$num);
    }

    public function hello(Request $request)
    {
        $this->Upload_S3("123.txt", "Hello");
        Storage::disk('local')->put('Upload_Pool/'.'123',base64_decode(123));
        return 1;
        return DB::table('queue_status')->where('id',1)->update(['status'=>'processing']);
        return $this->F(5);
        $a = $request->data;

        return $this->hello_re($a,0,0);
    }
    public function Upload_S3($filename,$content)
    {
        Storage::disk('s3')->put($filename,$content);
    }

    private function hello_re($array,$column,$row) //$a 3 4
    {
        if($column<count($array)&&$row<count($array[0]))
        {
            echo $array[$column][$row];
            $row++;
        }
        else if($row == count($array[0])) {
            $column++;
            $row = 0;
        }
        else
            return "end";
        return $this->hello_re($array,$column,$row);
    }

    private function recursive($sum,$num)
    {
        if ($num == 0)
            return $sum;
        else {
            $sum += $num%10;
            return $this->recursive($sum,$num/10);
        }
    }
    public function Rename(RenameRequest $request)//rename
    {
        $array = array();
        $array['status'] = 200;
        $api = $request->header('Api-Token');
        $servicename =$this->TokenRepository->GetServicebyToken($api);
        $data = $request->input('data');

        $uni_id = $data['uni_id'];
        $rename = $data['rename'];

        $document_seq_id = $this->SeqRepository->Generate_seq(2); //Document ID
        $this->fileRepository->Rename($uni_id,$rename,now(),$servicename);
        $this->fileRepository->Create_Document($document_seq_id,$uni_id,'Rename succeed.',$servicename);

        $hasfile = $this->fileRepository->GetFile($rename);
            if($hasfile!=='[]')
                $array['data'] = array($rename => 'Rename successfully!');
        return $array;
    }
    public function Delete(DownloadRequest $request)
    {
        $array = array();
        $array['status'] = 200;
        $api = $request->header('Api-Token');
        $servicename =$this->TokenRepository->GetServicebyToken($api);

        $data = $request->input('data');
        $uni_id = $data['uni_id'];
        $filename = $this->fileRepository->GetFileNamebyUniid($uni_id);

        $document_seq_id = $this->SeqRepository->Generate_seq(2); //Document ID
        $this->fileRepository->Create_Document($document_seq_id,$uni_id,'Delete '.$filename.' succeed.',$servicename);
        $this->fileRepository->UpdateName($uni_id,$filename, $servicename);
        $this->fileRepository->DeleteFile($uni_id,$filename);
        Storage::disk('s3')->delete($uni_id);

        if ($this->fileRepository->GetFileOnlyTrashed($filename)) { // show softdelete objects
            $array['data'] = array($filename  => 'Delete successfully!');
        } else {
            $array['data'] = array($filename  => 'Delete unsuccessfully!');
        }
        return $array;
    }

    public function Bulkdelete(BulkDeleteRequest $request)
    {
        $api = $request->header('Api-Token');
        $servicename =$this->TokenRepository->GetServicebyToken($api);
        $array['status'] = 200;
        $array['data'] = array();

        $Data = $request->data;
        foreach($Data as $data) {
            $uni_id = $data['uni_id'];
            $filename = $this->fileRepository->GetFileNamebyUniid($uni_id);
            $document_seq_id = $this->SeqRepository->Generate_seq(2); //Document ID
            $this->fileRepository->Create_Document($document_seq_id,$uni_id,'Delete '.$filename.' succeed.',$servicename);
            $this->fileRepository->UpdateName($uni_id,$filename, $servicename);
            $this->fileRepository->DeleteFile($uni_id,$filename);
            Storage::disk('s3')->delete($uni_id);

            if ($this->fileRepository->GetFileOnlyTrashed($filename)) { // show softdelete objects
                $input = array($filename  => 'Delete successfully!');
                array_push($array['data'],$input);
                $array['status'] = 200;
            } else {
                $input = array($filename  => 'Delete unsuccessfully!');
                array_push($array['data'],$input);
                $array['status'] = 500;
            }
        }
        return $array;
    }
}

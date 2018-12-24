<?php

namespace App\Http\Controllers;


use App\File;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Repositories\FileRepository;
use Psr\Log\NullLogger;

class GetApiController extends Controller
{
    protected $userRepository,$fileRepository;
    public function __construct(UserRepository $userRepository,FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->userRepository = $userRepository;
    }
    public function show()
    {
        return $this->fileRepository->Show();
    }
    public function search(Request $request)
    {
        $search = $request->input('string');
        if($search == NUll)
            return response()->json(['message' => 'String is required.'],400);
        $files = $this->fileRepository->Search($search);
        if($files->get()=="[]")
            return response()->json(['message' => 'No matches.'],404);
        else
            return $files->simplepaginate(10);
    }
    public function recycle_bin()
    {
        return $this->fileRepository->RecycleBin();
    }
    public function updated_at(){
        $files = File::orderBy('updated_at','asc')->simplepaginate(10);
        return $files;
    }
    public function filename(){
        $files = File::orderBy('name','asc')->simplepaginate(10);
        return $files;
    }
    public function size(){
        $files = File::orderBy('size','asc')->simplepaginate(10);
        return $files;
    }

}

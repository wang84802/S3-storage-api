<?php

namespace App\Http\Controllers;

use App\File;
use App\Repositories\UserRepository;
use App\Repositories\FileRepository;

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
    public function recycle_bin()
    {
        return $this->fileRepository->RecycleBin();
    }
    public function updated_at(){
        $files = File::orderBy('updated_at','asc')->simplepaginate(2);
        return $files;
    }
    public function filename(){
        $files = File::orderBy('name','asc')->simplepaginate(2);
        return $files;
    }
    public function size(){
        $files = File::orderBy('size','asc')->simplepaginate(2);
        return $files;
    }

}

<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Repositories\FileRepository;

class GetApiController extends Controller
{
    protected $userRepository,$fileRepository;
    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }
    public function show()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->fileRepository->Show($user->name);
    }
    public function search(Request $request)
    {
        $search = $request->input('string');
        if ($search == null) {
            return response()->json(['message' => 'String is required.'], 400);
        }
        $files = $this->fileRepository->Search($search);
        if ($files->get() == "[]") {
            return response()->json(['message' => 'No matches.'], 404);
        } else 
            return $files->paginate(5)->appends(['string'=>$search]);
    }
}

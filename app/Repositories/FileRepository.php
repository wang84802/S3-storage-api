<?php
namespace App\Repositories;

use App\File;
use Illuminate\Http\Request;
use App\Presenters\FilePresenter;

class FileRepository
{
    protected $file;

    public function GetFileNamebyUniid($uni_id)
    {
        return File::where('uni_id',$uni_id)->value('name');
    }
    public function Delete($filename)
    {
        return File::where('name',$filename)
            ->delete();
    }
    public function Rename($uni_id,$rename,$updated_at,$updated_by)
    {
        return File::where('uni_id',$uni_id)
            ->update(array(
                'name' => $rename,
                'updated_at' => $updated_at,
                'updated_by' => $updated_by,
            ));
    }public function File_Exist($name)
    {
        return File::where('name',$name)->exists();
    }
    public function GetFile($filename)
    {
        return File::where('name',$filename)->get();
    }
    public function DeleteFile($uni_id,$filename)
    {
        return File::where('uni_id',$uni_id)->where('name',$filename)->delete();
    }

    public function GetFileOnlyTrashed($filename)
    {
        return File::onlyTrashed()
            ->where('name',$filename)
            ->get();
    }
    public function Search($search)
    {
        return File::where('name','like','%'.$search.'%');
    }
    public function Show($username)
    {
        return File::select('name','size','uni_id','created_at')->where('created_by',$username)->whereNULL('deleted_at')->simplepaginate(10);
    }

    public function UpdateName($uni_id,$filename,$name)
    {
        return File::where('uni_id',$uni_id)->where('name',$filename)
            ->update(array('updated_by' => $name));
    }
};


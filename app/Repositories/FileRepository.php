<?php
namespace App\Repositories;

use App\File;
use Illuminate\Http\Request;
use App\Presenters\FilePresenter;

class FileRepository
{
    protected $file;
    public function __construct(File $file,FilePresenter $filePresenter)
    {
        $this->filePresenter = $filePresenter;
        $this->file = $file;
    }
    public function File($filename,$extension,$size,$created_by,$updated_by)
    {
        $size = $this->filePresenter->getsize($size);
        return $this->file
            ->create([
                'name' => $filename,
                'extension' => $extension,
                'size' => $size,
                'created_by' => $created_by,
                'updated_by' => $updated_by,
            ]);
    }
    public function Delete($filename)
    {
        return $this->file
            ->where('name',$filename)
            ->delete();
    }
    public function Rename($originalname,$rename,$extension,$updated_at,$updated_by)
    {
        return $this->file
            ->where('name',$originalname)
            ->update(array(
                'name' => $rename,
                'extension' => $extension,
                'updated_at' => $updated_at,
                'updated_by' => $updated_by,
            ));
    }
    public function GetFile($filename,$extension)
    {
        return $this->file
            ->where('name',$filename)
            ->where('extension',$extension);
    }
    public function GetFileWithTrashed($id,$filename,$extension)
    {
        return File::withTrashed()->where([
            'id' => $id,
            'name' => $filename,
            'extension' => $extension
        ]);
    }
    public function GetFileOnlyTrashed($filename,$extension)
    {
        return $this->file->onlyTrashed()
            ->where('name',$filename)
            ->where('extension',$extension)
            ->get();
    }
    public function Search($search)
    {
        return $this->file
            ->where('name','like','%'.$search.'%');
    }
    public function Show()
    {
        return $this->file
            ->select('name','extension','size','updated_at')->whereNULL('deleted_at')->simplepaginate(10);
    }
    public function RecycleBin()
    {
        return $this->file->onlyTrashed()
            ->select('id','name','extension','size','updated_at')->get();
    }
    public function UpdateName($filename,$extension,$name)
    {
        return $this->file
            ->where('name',$filename)
            ->where('extension',$extension)
            ->update(array('updated_by' => $name));
    }
};


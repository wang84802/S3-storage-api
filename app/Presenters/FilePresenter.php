<?php
namespace App\Presenters;

class FilePresenter
{
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

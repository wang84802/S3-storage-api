<?php
namespace App\Examiners;

use DB;
use Storage;
use Notification;
use App\Exceptions\FailedException;
use App\Repositories\FileRepository;
use App\Notifications\PoolNotification;

class Examiner {
    public static function ServerBusy($pool)
    {
        $files = Storage::disk('local')->files($pool);
        $files_with_size = 0;
        foreach ($files as $key => $file)
            if(self::Storage_Exist($file))
                $files_with_size += Storage::disk('local')->size($file);
        if($files_with_size>209715200) //104857600 100M
            self::Pool_Notify($pool,$files_with_size);
        if($files_with_size>536870912) //536870912 512MB
            throw new FailedException('400','server','400049105','Server is busy.');
    }

    public static function Base64Invalid($content)
    {
        if (base64_encode(base64_decode($content)) != $content)
            throw new FailedException('400','data.content','400049995','The data.content field is invalid.');
    }

    public static function UniidInvalid($uni_id)
    {
        return DB::table('files')->where(['uni_id' => $uni_id,'deleted_at' => NULL])->exists();
    }

    public static function JobCheck($starttime,$uni_id,$pool)
    {
        $FileRepository = new FileRepository();
        $timeout = false;
        do {
            $endtime = microtime(true);
            if($endtime-$starttime>5) {
                $timeout = true;
                break;
            }
        } while(!(self::Storage_Exist($pool.'/'.$uni_id) && ($FileRepository->Document_Exist($uni_id) != NULL)));
        return $timeout;
    }
    private static function Storage_Exist($file_path)
    {
        return Storage::disk('local')->exists($file_path);
    }

    private static function Pool_Notify($storage,$size)
    {
        Notification::route('slack', 'https://hooks.slack.com/services/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')->notify(new PoolNotification($storage,$size));
    }
}
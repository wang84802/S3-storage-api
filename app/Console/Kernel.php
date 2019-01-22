<?php

namespace App\Console;

use DB;
use Log;
use Storage;
use App\File;
use Carbon\Carbon;
use Notification;
use App\Notifications\PoolNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Clean::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /* storage-pool flush every 5 mins */
//        $a = strtotime('09:00:00');
//        $b = strtotime('18:00:00');
//        Log::info(now());
//        if($b>time() && time()>$a)
        {
            if(DB::table('queue_status')->where('id',1)->value('status') == 'processed')
            {
                $schedule->call(function () {
                    $download = Storage::disk('local')->directories('Download_Pool');
                    if(count($download) != 0)
                    {
                        for ($i = 0 ; $i <= count($download)-1 ; $i++)
                            Storage::disk('local')->delete($download[$i]);
                        log::info('Download pool is clean.');
                    }
                    $upload = Storage::disk('local')->files('Upload_Pool');
                    if(count($upload) != 0)
                    {
                        for ($i = 0 ; $i <= count($upload)-1 ; $i++)
                            Storage::disk('local')->delete($upload[$i]);
                        log::info('Upload pool is clean.');
                    }
                });
            }
            else
                log::info('Queue is busy.');
        }
        /* recycle-bin auto-delete in 30 days *///
//        $files = File::onlyTrashed()->get();
//        foreach ($files as $file) {
//            $now_time = Carbon::now()->toDateTimeString();
//            if((strtotime($now_time)-strtotime($file->deleted_at))/86400>30)
//            {
//                Log::info($file->name.'.'.$file->extension.' '.(strtotime($now_time)-strtotime($file->deleted_at))/86400);
//                $file->forceDelete();
//                Storage::disk('s3')->delete($file->name . '.' . $file->extension);
//            }
//        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

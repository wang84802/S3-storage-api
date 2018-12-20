<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;
use Storage;

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
        $schedule->call(function () {
            $download = Storage::disk('local')->files('Download_Pool');
            for ($i = 0 ; $i <= count($download)-1 ; $i++)
                Storage::disk('local')->delete($download[$i]);
            $upload = Storage::disk('local')->files('Upload_Pool');
            for ($i = 0 ; $i <= count($upload)-1 ; $i++)
                Storage::disk('local')->delete($upload[$i]);
        })->everyMinute();
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

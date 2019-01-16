<?php

namespace App\Providers;

use DB;
use Log;
use App\Document;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

use App\Notifications\JobFailedNotification;
use Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);//password_Reset database
        LengthAwarePaginator::defaultView('vendor.pagination.default');

        Queue::before(function(JobProcessing $event){
            //Log::info('before');
            //log::info(DB::table('queue_status')->where('id',1)->value('status'));
        });
        Queue::after(function(JobProcessed $event){

        });

        Queue::failing(function(JobFailed $event){
            Document::create([
                'job_id' => $event->job->getJobId(),
                'file' => $event->exception,//$event->connectionName, $event->job->getQueue(),$event->job->getRawBody(), $event->exception
            ]);

            $eventData = [];
            $eventData['connectionName'] = $event->connectionName;
            $eventData['job'] = $event->job->resolveName();
            $eventData['queue'] = $event->job->getQueue();
            $eventData['exception'] = [];
            $eventData['exception']['message'] = $event->exception->getMessage();
            $eventData['exception']['file'] = $event->exception->getFile();
            $eventData['exception']['line'] = $event->exception->getLine();

            //Log::info($event->job->getJobId());
            $eventData['id'] = $event->job->getJobId();

            Notification::route('slack', 'https://hooks.slack.com/services/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')->notify(new JobFailedNotification($eventData));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

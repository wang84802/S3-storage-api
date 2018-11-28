<?php

namespace App\Providers;

use Log;
use App\Document;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

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
//        Queue::before(function(JobProcessing $event){
//            log::info('before: '.$event->job->getName());
//        });
//        Queue::after(function(JobProcessed $event){
//            log::info('after:'.$event->job->hasFailed());
//        });
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

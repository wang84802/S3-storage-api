<?php
namespace App\Repositories;

use DB;

class StatusRepository
{
    public function Queue_processing()
    {
        DB::table('queue_status')->where('id',1)->update(['status'=>'processing']);
    }
    public function Queue_processed()
    {
        DB::table('queue_status')->where('id',1)->update(['status'=>'processed']);
    }

};


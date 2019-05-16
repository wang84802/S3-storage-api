<?php
namespace App\Repositories;

use DB;
use App\Helpers\RedisHelper;
use Illuminate\Http\Request;

class SeqRepository
{
    public function Generate_seq($type)
    {
        $lockKey = $type === 1 ?
            'storage:file_storage_seq':
            'storage:document_storage_seq';

        while (true) {
            if (RedisHelper::lock($lockKey)) {
                $seq = DB::connection('seq_db')
                    ->select(
                        DB::raw("select(currval_storage({$type}, 1)) as seq")
                    );

                RedisHelper::unlock($lockKey);

                return $seq[0]->seq;
            }

            usleep(100);
        }
    }
//    public function Generate_seq($type)
//    {
//        $seq = DB::connection('seq_db')
//            ->select(
//                DB::raw("select(currval_storage({$type}, 1)) as seq")
//            );
//        return $seq[0]->seq;
//    }
};


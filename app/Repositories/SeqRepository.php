<?php
namespace App\Repositories;

use DB;
use Illuminate\Http\Request;

class SeqRepository
{
    public function Generate_seq($type)
    {
        $seq = DB::connection('seq_db')
            ->select(
                DB::raw("select(currval_storage({$type}, 1)) as seq")
            );
        return $seq[0]->seq;
    }
};


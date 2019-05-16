<?php

namespace App\Jobs;

use Log;

use App\Repositories\SeqRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class SeqJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $SeqRepository = new SeqRepository();
        $file_seq_id = $SeqRepository->Generate_seq(1); //File ID
        Log::info($file_seq_id);
    }

}


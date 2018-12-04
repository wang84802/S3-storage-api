<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class Clean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean documents';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::table('documents')->where('file','File does not exist!')->delete();
    }
}

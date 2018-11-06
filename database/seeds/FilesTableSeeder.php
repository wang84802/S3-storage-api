<?php

require_once '/var/www/html/S3/vendor/fzaninotto/faker/src/autoload.php';

use Illuminate\Database\Seeder;
use App\File as File;

class FilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1;$i<=1;$i++)
        {
            File::create([
                'name' => str_random(5),
                'extension' => 'txt',
                'size' => '11',
                'created_by' => 'test456',
                'updated_by' => 'test456',
            ]);
        }

    }
}

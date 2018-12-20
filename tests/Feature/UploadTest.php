<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\File;
use App\Jobs\test_upload;
use Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Contracts\Bus\Dispatcher;

class UploadTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function NotAuthenticated()
    {
        $this->json('POST', 'api/TaskUpload')
            ->assertStatus(401);
    }

    public function testOrderShipping()
    {
        Queue::fake();
        $data = '{
	        "data": [
                
            ]
        }';
        $api = '';
        dispatch(new test_upload($data,$api));

        Queue::assertPushed(test_upload::class);
    }
}

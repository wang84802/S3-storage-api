<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\TestUpload;
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
//        Queue::assertPushed(CallQueuedListener::class, function ($job){});
        $data = '{
	        "data": [

            ]
        }';
        $api = '';
        dispatch(new TestUpload($data,$api));
//
        //Queue::assertNotPushed(Dispatcher::class);
        $this->expectsJobs(TestUpload::class);
    }
}

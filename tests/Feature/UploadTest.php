<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\File;
use App\Jobs\test_upload;
use Storage;
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
//        $user = factory(User::class)->make();
//        $token = $user->generateToken();
//        $headers = ['Api-Token' => $token];
//        $payload =
//        array(
//            'filename' => 'test1',
//            'extension' => 'txt',
//            'content' => 'aGVsbG8gd29ybGQ='
//        );
//        $job = (new test_upload($payload,$token));

        Queue::assertNotPushed(test_upload::class);
//        Queue::assertPushed(test_upload::class,function ($job){
//            return strlen($job->message) < 140;
//
//        });
    }
}

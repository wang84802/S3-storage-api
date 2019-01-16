<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Log;

class JobFailedNotification extends Notification
{
    use Queueable;
    private $event;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
        ->from('https://hooks.slack.com/services/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')
        ->to(env('queue-notification'))
        ->image('https://placeimg.com/48/48/any')
        ->error()
        ->content('Queued job failed: ' . $this->event['job'])
        ->attachment(function ($attachment) {
            $attachment->title($this->event['exception']['message'])
                ->fields([
                    'Project' => 'S3',
                    'Job_Id' => $this->event['id'],
                    'File' => $this->event['exception']['file'],
                    'Line' => $this->event['exception']['line'],
                    'Server' => env('APP_ENV'),
                    'Queue' => $this->event['queue'],
                ]);
        });

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

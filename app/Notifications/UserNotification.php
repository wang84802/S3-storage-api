<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

class UserNotification extends Notification
{
    use Queueable;
    private $user,$message;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$message)
    {
        $this->user = $user;
        $this->message = $message;
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
            ->to(env('SLACK_CHANNEL2'))
            ->attachment(function ($attachment) {
                $attachment->title($this->message)
                    ->fields([
                        'id' => $this->user['id'],
                        'name' => $this->user['name'],
                        'email' => $this->user['email'],
                        'created_at' => $this->user['created_at'],
                        'updated_at' => $this->user['updated_at'],
                        'type' => $this->user['type'],
                        'api_token' => $this->user['api_token'],
                        'status' => $this->user['status'],
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

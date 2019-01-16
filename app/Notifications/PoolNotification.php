<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use App\Presenters\FilePresenter;

class PoolNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message,$size)
    {
        $this->message = $message;
        $this->size = $size;
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
        $FilePresenter = new FilePresenter();
        $this->size = $FilePresenter->getsize($this->size);
        return (new SlackMessage)
            ->from('https://hooks.slack.com/services/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')
            ->to('storage-api')
            ->attachment(function ($attachment) {
                $attachment->title($this->message)
                    ->fields([
                        'Current Size' => $this->size,
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

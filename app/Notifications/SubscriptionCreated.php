<?php

namespace App\Notifications;

use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubscriptionCreated extends Notification
{
    use Queueable;

    private $storesCount;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $storesCount)
    {
        $this->storesCount = $storesCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TwilioChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toTwilio($notifiable)
    {
        $appName = config('app.name');
        $storesCountDescription = $this->storesCount === 1
            ? $this->storesCount . ' store'
            : $this->storesCount . ' stores';

        return (new TwilioSmsMessage())
            ->content('Hello! This is ' . $appName . '. We\'re now monitoring ' . $storesCountDescription . ' for you. We\'ll let you know as soon as a desired curbside pickup slot becomes available. No longer wish to hear from us? Just reply with STOP');
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

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
    private $isRepeat;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $storesCount, bool $isRepeat = false)
    {
        $this->storesCount = $storesCount;
        $this->isRepeat = $isRepeat;
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

        $intro = $this->isRepeat
            ? 'Got it. We\'ll continue to monitor'
            : 'Hello! This is ' . $appName . '. We\'re now monitoring';

        $message = implode(PHP_EOL, [
            $intro . ' ' . $storesCountDescription . ' for you. We\'ll let you know as soon as a desired curbside pickup slot becomes available.',
            'Need to change your search criteria? Just head to https://curb.run',
            'No longer wish to hear from us? Just reply with DONE.'
        ]);

        return (new TwilioSmsMessage())
            ->content($message);
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

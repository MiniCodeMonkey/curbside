<?php

namespace App\Notifications;

use Carbon\Carbon;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Store;
use App\Subscriber;

class TimeslotsFound extends Notification
{
    use Queueable;

    private $timeslots;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($timeslots)
    {
        $this->timeslots = $timeslots;
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
        $message = implode(PHP_EOL, [
            'Great news! Curbside pickup slots available.',
            $this->formatSlots($notifiable)->join(PHP_EOL),
            'We will stop notifying you now unless you reply with CONTINUE'
        ]);

        info($notifiable->phone .': ' . $message);

        return (new TwilioSmsMessage())
            ->content($message);
    }

    private function formatSlots(Subscriber $subscriber) {
        return $this->timeslots->take(5)->map(function ($timeslot, $storeId) use ($subscriber) {
            $store = Store::with('chain')->find($storeId);

            $distanceDescription = '(~' . round($subscriber->distanceTo($store)) . ' miles away)';

            return implode(PHP_EOL, [
                $store->chain->name . ' ' . $store->name . ' ' . $distanceDescription,
                $timeslot->date->format('l, M j') . ' ' . $this->formatTime($timeslot->from) . ' - ' . $this->formatTime($timeslot->to)
            ]) . PHP_EOL;
        });
    }

    private function formatTime($time) {
        return Carbon::parse($time)->format('g:i a');
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

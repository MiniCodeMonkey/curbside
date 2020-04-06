<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subscriber;
use App\Notifications\SubscriptionCreated;
use App\Notifications\SubscriptionCancelled;
use App\Notifications\StatusUpdate;

class TwilioWebhookController extends Controller
{
    public function __invoke(Request $request) {
        $phone = $request->input('From');
        $message = trim($request->input('Body'));

        $subscriber = Subscriber::where('phone', $phone)->first();

        if ($subscriber) {
            $this->handleAction($subscriber, $message);
        }
    }

    private function handleAction(Subscriber $subscriber, string $message) {
        switch ($message) {
            case 'CONTINUE':
                if ($subscriber->status !== 'ACTIVE') {
                    $subscriber->status = 'ACTIVE';
                    $subscriber->save();

                    $subscriber->notify(new SubscriptionCreated($subscriber->stores()->count(), true));
                }
                break;

            case 'DONE':
                if ($subscriber->status === 'ACTIVE') {
                    $subscriber->status = 'INACTIVE';
                    $subscriber->save();

                    $subscriber->notify(new SubscriptionCancelled());
                }
                break;

            case 'STATUS':
                $subscriber->notify(new StatusUpdate());
                break;
        }
    }
}

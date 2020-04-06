<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Notifications\TimeslotsFound;
use App\Chain;
use App\Subscriber;
use App\Store;
use App\Timeslot;
use Carbon\Carbon;

class ScanChain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $chain;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Chain $chain)
    {
        $this->chain = $chain;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $timeslots = $this->chain->stores()
            ->whereHas('subscribers', function (Builder $query) {
                $query->where('status', 'ACTIVE');
            })
            ->get()
            ->mapWithKeys(function (Store $store) {
                return [$store->id => $store->scanPickupSlots()];
            });

        // TODO

        $subscribers = Subscriber::active()
            ->with('stores')
            ->each(function (Subscriber $subscriber) use ($timeslots) {
                $this->matchToTimeslots($subscriber, $timeslots);
            });
    }

    private function matchToTimeslots(Subscriber $subscriber, Collection $timeslots) {
        $subscribedStoreIds = $subscriber->stores()->pluck('stores.id')->toArray();

        $timeslots = $timeslots
            // Timeslots for stores that the user subscribed to
            ->filter(function ($timeslot, $storeId) use ($subscribedStoreIds) {
                return in_array($storeId, $subscribedStoreIds);
            })
            // Timeslots that are within the users set time criteria threshold
            ->filter(function ($timeslot) use ($subscriber) {
                return (
                    $subscriber->criteria === 'ANYTIME' ||
                    ($subscriber->criteria === 'SOON' && $timeslot->date->diffInDays() <= 3) ||
                    ($subscriber->criteria === 'TODAY' && $timeslot->date->isToday())
                );
            })
            ->sortBy(function ($timeslot) {
                return Carbon::parse($timeslot->date->format('Y-m-d') . ' ' . $timeslot->from);
            });

        if ($timeslots->count() > 0) {
            $subscriber->status = 'PAUSED';
            $subscriber->save();

            $subscriber->notify(new TimeslotsFound($timeslots));
        }
    }
}

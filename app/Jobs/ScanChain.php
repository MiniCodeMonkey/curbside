<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Cache\FileStore;
use App\Notifications\TimeslotsFound;
use App\Chain;
use App\Subscriber;
use App\Store;
use App\Timeslot;
use Carbon\Carbon;

class ScanChain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const LOCK_DURATION_SECONDS = 600; // 10 minutes

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
        $cache = cache();

        if (app()->environment('local')) {
            info('Warning: Not acquiring lock for ' . __CLASS__);
            $this->scan();
        } else {
            $lock = $cache->lock(__CLASS__ . $this->chain->id, self::LOCK_DURATION_SECONDS);

            if ($lock->get()) {
                try {
                    $this->scan();
                } finally {
                    $lock->release();
                }
            } else {
                info('Could not get lock for ' . __CLASS__ . ': ' . $this->chain->name);
            }
        }
    }

    private function scan()
    {
        $storeScanner = $this->chain->getStoreScanner();

        $stores = $this->chain->stores()
            ->whereHas('subscribers', function (Builder $query) {
                $query->where('status', 'ACTIVE');
            })
            ->get();

        info('Scanning for timeslots at ' . $stores->count() . ' ' . $this->chain->name . ' stores');

        $before = microtime(true);
        $timeslots = $stores->flatMap(function (Store $store) use ($storeScanner) {
            $timeslots = $storeScanner->scan($store);
            info($timeslots->count() . ' timeslot(s) found for ' . $store->chain->name . ' ' . $store->name);

            return $timeslots;
        });
        $after = microtime(true);
        info('Completed ' . $this->chain->name . ' scan in ' . round(($after - $before) / 60) . ' minute(s) with a total of ' . $timeslots->count() . ' timeslot(s) found.');

        $subscribers = Subscriber::active()
            ->with('stores')
            ->get();

        info('Looking through ' . $subscribers->count() . ' subscribers');

        $subscribers->each(function (Subscriber $subscriber) use ($timeslots) {
            $this->matchToTimeslots($subscriber, $timeslots);
        });
    }

    private function matchToTimeslots(Subscriber $subscriber, Collection $timeslots)
    {
        $subscribedStoreIds = $subscriber->stores()->pluck('stores.id')->toArray();

        $timeslots = $timeslots
            // Timeslots for stores that the user subscribed to
            ->filter(function ($timeslot) use ($subscribedStoreIds) {
                return in_array($timeslot->store_id, $subscribedStoreIds);
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
            info('Found ' . $timeslots->count() . ' timeslot(s) for subscriber #' . $subscriber->id);

            $subscriber->status = 'PAUSED';
            $subscriber->save();

            info('Notifying subscriber #' . $subscriber->id);
            $subscriber->notify(new TimeslotsFound($timeslots));
        }
    }
}

<?php

namespace App;

use RuntimeException;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Store;

class KrogerStoreScanner extends StoreScanner
{
    protected $baseUri = 'https://www.kroger.com/fulfillment/api/v1/';

    protected function prepareSession() {
        if (!app()->environment('production')) {
            // Randomize start to ensure that all Kroger chain scanners do not execute at the same time
            $sleepDuration = mt_rand(10, 120);
            info('[Kroger] Waiting ' . $sleepDuration . ' seconds');
            sleep($sleepDuration);
        }
    }

    public function scan(Collection $stores): ?Collection {
        parent::scan($stores);

        $stores = $stores->keyBy('identifier');

        $response = json_decode((string)$this->client->get('timeslots/list', [
            'query' => [
                'stores' => $stores->keys()->implode(','),
                'totalStores' => $stores->count(),
                'fulfillment' => 'CurbSide',
                'banner' => $this->getBanner($stores->first()->chain->name)
            ],
            'headers' => [
                'accept' => 'application/json, text/plain, */*',
                'accept-encoding' => 'gzip, deflate, br',
                'content-type' => 'application/json;charset=UTF-8',
                'origin' => 'https://www.kroger.com',
                'referer' => 'https://www.kroger.com/stores/search?searchText=virginia&selectedStoreFilters=Pickup',
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36'
            ]
        ])->getBody());

        $timeslots = collect($response->timeSlots)->flatMap(function ($date) use ($stores) {
            return collect($date->times)->map(function ($item) use ($date, $stores) {
                if (!$item->available) {
                    return null;
                }

                return Timeslot::updateOrCreate([
                    'store_id' => $stores->get($item->storeId)->id,
                    'date' => $date->pickupDate,
                    'from' => $item->pickupBeginTime,
                    'to' => Carbon::parse($item->pickupBeginTime)->addHour()->format('H:i')
                ]);
            })->filter();
        })->filter();

        return $timeslots;
    }

    private function getBanner(string $chainName): string {
        $banners = [
            "City Market" => "citymarket",
            "Dillons" => "dillons",
            "Foods Co" => "foodsco",
            "Food 4 Less" => "food4less",
            "Fred Meyer" => "fredmeyer",
            "Fry's" => "frysfood",
            "Gerbes" => "gerbes",
            "JayC Foods Stores" => "jaycfoods",
            "King Soopers" => "kingsoopers",
            "Kroger" => "kroger",
            "Owen's Market" => "owensmarket",
            "Pay-Less" => "pay-less",
            "Postal Prescription Services" => "ppsrx",
            "QFC" => "qfc",
            "Ralphs" => "ralphs",
            "Smith's" => "smithsfoodanddrug",
            "Kwik Shop" => "kwikshop",
            "Loaf 'N Jug" => "loafnjug",
            "Quik Stop" => "quikstop",
            "Tom Thumb" => "tomt",
            "Turkey Hill Mini Markets" => "turkeyhillstores",
            "Copps" => "copps",
            "Marianos" => "marianos",
            "Pick 'n Save" => "picknsave",
            "Metro Market" => "metromarket",
            "Harris Teeter" => "harristeeter",
            "Ruler Foods" => "rulerfoods",
            "The Little Clinic" => "thelittleclinic",
            "Harris Teeter Pharmacy" => "harristeeterpharmacy"
        ];

        return $banners[$chainName] ?? 'kroger';
    }
}

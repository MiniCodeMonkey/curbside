<?php

namespace App;

use RuntimeException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Store;

class HarrisTeeterStoreScanner extends StoreScanner
{
    protected $baseUri = 'https://www.harristeeter.com/';

    protected function prepareSession() {
        // Establish cookies first
        $this->client->get('/');

        $this->client->post('api/checkLogin', [
            'headers' => [
                'referer' => 'https://www.harristeeter.com/order-online/groceries',
            ],
            'json' => []
        ]);

        $response = $this->client->post('api/login', [
            'headers' => [
                'referer' => 'https://www.harristeeter.com/user/login',
            ],
            'json' => [
                'email' => config('services.harristeeter.email'),
                'Password' => config('services.harristeeter.password')
            ]
        ]);
        $user = json_decode((string)$response->getBody());

        if ($user->Data->loggedInEmail != config('services.harristeeter.email')) {
            throw new RuntimeException('Harris Teeter: Login failure');
        }
    }

    private function changeStore(Store $store) {
        $this->client->post('shop/api/checkLogin', [
            'headers' => [
                'referer' => 'https://www.harristeeter.com/shop/store/262',
            ],
            'query' => [
                'Email' => config('services.harristeeter.email')
            ],
            'json' => [
                'pseudoStoreId' => $store->identifier
            ]
        ]);
    }

    public function scan(Collection $stores): ?Collection {
        parent::scan($stores);

        return $stores->flatMap(function (Store $store) {
            $this->changeStore($store);

            $path = 'shop/api/v1/el/stores/' . $store->identifier . '/fulfillments/pickup/times?Email=' . config('services.harristeeter.email');
            $response = json_decode((string)$this->client->get($path, [
                'headers' => [
                    'referer' => 'https://www.harristeeter.com/shop/store/383/reserve-timeslot',
                ],
            ])->getBody());

            $timeslots = collect($response->Data->AvailableTimes)
                ->filter(function ($item) {
                    return $item->AvailableSlotCount > 0;
                })
                ->map(function ($item) use ($store) {
                    $startTime = Carbon::parse($item->StartTime);
                    $endTime = Carbon::parse($item->EndTime);

                    return Timeslot::updateOrCreate([
                        'store_id' => $store->id,
                        'date' => $startTime->format('Y-m-d'),
                        'from' => $startTime,
                        'to' => $endTime,
                    ]);
                });

            return $timeslots;
        });
    }
}

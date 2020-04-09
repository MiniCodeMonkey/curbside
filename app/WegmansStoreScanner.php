<?php

namespace App;

use RuntimeException;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Store;

class WegmansStoreScanner extends StoreScanner
{
    protected $baseUri = 'https://shop.wegmans.com/api/v2/';

    protected function prepareSession() {
        $this->client->post('user_sessions', [
            'json' => [
                'binary' => 'web-ecom',
                'binary_version' => '2.25.121',
                'is_retina' => false,
                'os_version' => 'MacIntel',
                'pixel_density' => '2.0',
                'push_token' => '',
                'screen_height' => 1440,
                'screen_width' => 2560,
            ],
            'headers' => [
                'user-context' => base64_encode(json_encode(['StoreId' => null]))
            ]
        ]);

        $this->client->post('users');
    }

    private function changeStore(Store $store) {
        $response = $this->client->patch('user', [
            'json' => ['store_id' => $store->identifier]
        ]);

        $user = json_decode((string)$response->getBody());
        if ($user->store->id != $store->identifier) {
            throw new RuntimeException('Wegmans: Expected store id to be ' . $store->identifier . ' but got ' . $user->store->id);
        }
    }

    public function scan(Collection $stores): ?Collection {
        parent::scan($stores);

        return $stores->flatMap(function (Store $store) {
            $this->changeStore($store);

            $cart = json_decode((string)$this->client->get('cart')->getBody());

            $response = json_decode((string)$this->client->get('timeslots', ['query' => [
                'above_threshold' => true,
                'cart_id' => $cart->id,
                'fulfillment_type' => 'pickup',
                'user_timezone' => 'America%2FNew_York'
            ]])->getBody());

            $timeslots = collect($response->items)->map(function ($item) use ($store) {
                return Timeslot::updateOrCreate([
                    'store_id' => $store->id,
                    'date' => Carbon::parse($item->fulfillment_date)->format('Y-m-d'),
                    'from' => $item->timeslot->from_time,
                    'to' => $item->timeslot->to_time
                ]);
            });

            return $timeslots;
        });
    }
}

<?php

namespace App;

use RuntimeException;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Store;

class FoodLionStoreScanner extends StoreScanner
{
    protected $baseUri = 'https://shop.foodlion.com/api/v2/';

    // As of 4/9/2020 Food Lion has an incomplete SSL chain
    // as verified by ssllabs.com
    // There's nothing high-risk in this request, so we're fine
    // with disabling SSL
    protected $enableSSLVerification = false;

    private $bearerToken;

    protected function prepareSession() {
        $response = $this->client->post('user_sessions', [
            'json' => [
                'binary' => 'web-ecom',
                'binary_version' => '2.25.131',
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
        $json = json_decode((string)$response->getBody());
        $this->bearerToken = $json->session_token;

        $this->client->post('users', [
            'headers' => $this->getHeaders()
        ]);
    }

    private function getHeaders() {
        return [
            'Authorization' => 'Bearer ' . $this->bearerToken
        ];
    }

    private function changeStore(Store $store) {
        $response = $this->client->patch('user', [
            'json' => ['store_id' => $store->identifier],
            'headers' => $this->getHeaders()
        ]);

        $user = json_decode((string)$response->getBody());
        if ($user->store->id != $store->identifier) {
            throw new RuntimeException('FoodLion: Expected store id to be ' . $store->identifier . ' but got ' . $user->store->id);
        }
    }

    public function scan(Collection $stores): ?Collection {
        parent::scan($stores);

        return $stores->flatMap(function (Store $store) {
            $this->changeStore($store);

            $cart = json_decode((string)$this->client->get('cart', [
                'headers' => $this->getHeaders()
            ])->getBody());

            $response = json_decode((string)$this->client->get('timeslots', [
                'query' => [
                    'above_threshold' => true,
                    'cart_id' => $cart->id,
                    'fulfillment_type' => 'pickup',
                    'user_timezone' => 'America%2FNew_York'
                ],
                'headers' => $this->getHeaders()
            ])->getBody());

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

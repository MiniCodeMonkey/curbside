<?php

namespace App;

use RuntimeException;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Store;

class WeisMarketsStoreScanner extends StoreScanner
{
    protected $baseUri = 'https://www.weismarkets.com/api/';

    private $csrfToken;
    private $orderId;

    protected function prepareSession() {
        $json = json_decode((string)$this->client->post('m_user/sessioninit')->getBody());
        $this->csrfToken = $json[0] ?? null;

        $json = json_decode((string)$this->client->post('m_order', [
            'headers' => $this->getHeaders()
        ])->getBody());
        $this->orderId = $json->order_id;
    }

    private function getHeaders() {
        return [
            'x-csrf-token' => $this->csrfToken
        ];
    }

    private function changeStore(Store $store) {
        $this->client->post('m_order/' . $this->orderId . '/setpickuplocation', [
            'json' => [
                'location_id' => $store->identifier
            ],
            'headers' => $this->getHeaders()
        ]);

        $json = json_decode((string)$this->client->post('m_order/getinfo', [
            'json' => [
                'order_id' => $this->orderId
            ],
            'headers' => $this->getHeaders()
        ])->getBody());

        // Add item to cart
        // This is necessary to view timeslots, but cxan only be done after selecting a location
        $this->client->post('m_order/' . $this->orderId . '/addorderitem', [
            'json' => [
                'product_id' => 118270, // Cucumber
                'quantity' => 1,
            ],
            'headers' => $this->getHeaders()
        ]);

        if ($json->location->location_id != $store->identifier) {
            throw new RuntimeException('WeisMarkets: Expected store id to be ' . $store->identifier . ' but got ' . $user->store->id);
        }
    }

    public function scan(Collection $stores): ?Collection {
        parent::scan($stores);

        return $stores->flatMap(function (Store $store) {
            $this->changeStore($store);

            $json = json_decode((string)$this->client->post('m_order/' . $this->orderId . '/getduetimes', [
                'json' => [
                    'date' => now('America/New_York')->format('Y-m-d'),
                    'pagesize' => 14
                ],
                'headers' => $this->getHeaders()
            ])->getBody());

            return collect($json->dates)->flatMap(function ($date) use ($store) {
                return collect($date->times)->map(function ($item) use ($store, $date) {
                    if ($item->status !== 'available') {
                        return null;
                    }

                    return Timeslot::updateOrCreate([
                        'store_id' => $store->id,
                        'date' => Carbon::parse($date->date)->format('Y-m-d'),
                        'from' => Carbon::parse($item->from)->format('H:i:s'),
                        'to' => Carbon::parse($item->to)->format('H:i:s')
                    ]);
                });
            })->filter();
        })->filter();
    }
}

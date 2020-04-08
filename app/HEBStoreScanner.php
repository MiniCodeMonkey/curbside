<?php

namespace App;

use RuntimeException;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Store;

class HEBStoreScanner extends StoreScanner
{
    protected $baseUri = 'https://www.heb.com/commerce-api/v1/';

    public function scan(Store $store): ?Collection {
        parent::scan($store);

        $response = json_decode((string)$this->client->get('timeslot/timeslots', [
            'query' => [
                'store_id' => $store->identifier,
                'days' => 15,
                'fulfillment_type' => 'pickup'
            ]
        ])->getBody());

        $timeslots = collect($response->items)->map(function ($item) use ($store) {
            return Timeslot::updateOrCreate([
                'store_id' => $store->id,
                'date' => $item->date,
                'from' => $item->timeslot->from_time,
                'to' => $item->timeslot->to_time
            ]);
        });

        return $timeslots;
    }
}

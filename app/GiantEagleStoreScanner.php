<?php

namespace App;

use RuntimeException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Store;

class GiantEagleStoreScanner extends StoreScanner
{
    protected $baseUri = 'https://adapter.shop.gianteagle.com';

    private function changeStore(Store $store) {

        $query = <<<'GRAPHQL'
mutation AppRootRendererChangeStoreMutation(
  $fulfillmentMethod: FulfillmentMethod!
  $storeCode: String
  $storeSlug: String
  ) {
  changeStore(fulfillmentMethod: $fulfillmentMethod, storeCode: $storeCode, storeSlug: $storeSlug) {
    id
    store {
      name
      code
      slug
      id
    }
    fulfillmentMethod
  }
}
GRAPHQL;

        $json = json_decode((string)$this->client->post('api', [
            'json' => [
                'query' => $query,
                'variables' => [
                    'fulfillmentMethod' => 'pickup',
                    'storeCode' => null,
                    'storeSlug' => $store->identifier
                ]
            ]
        ])->getBody());

        if ($json->data->changeStore->store->slug != $store->identifier) {
            throw new RuntimeException('GiantEagle: Expected store id to be ' . $store->identifier . ' but got ' . $user->store->id);
        }
    }

    public function scan(Collection $stores): ?Collection {
        parent::scan($stores);

        return $stores->flatMap(function (Store $store) {
            $this->changeStore($store);

$query = <<<'GRAPHQL'
query HeaderAvailability_tooltipAvailabilityQuery {
  pickup: availableTimeSlotsForStore(fulfillmentMethod: pickup) {
    id
    fulfillmentDate {
      dateIso
    }
    timeSlots {
      id
      dateIso
      startTime
      available
    }
  }
  delivery: availableTimeSlotsForStore(fulfillmentMethod: delivery) {
    id
    fulfillmentDate {
      dateIso
    }
    timeSlots {
      id
      dateIso
      startTime
      available
    }
  }
}
GRAPHQL;

            $json = json_decode((string)$this->client->post('api', [
                'json' => [
                    'query' => $query,
                    'variables' => (object)[]
                ]
            ])->getBody());

            return collect($json->data->pickup)->flatMap(function ($date) use ($store) {
                return collect($date->timeSlots)->map(function ($item) use ($store) {
                    if (!$item->available) {
                        return null;
                    }

                    $timeslotDate = Carbon::parse($item->dateIso);
                    $from = Carbon::parse($item->startTime);

                    return Timeslot::updateOrCreate([
                        'store_id' => $store->id,
                        'date' => $timeslotDate,
                        'from' => $from,
                        'to' => $from->copy()->addHour()
                    ]);
                });
            })->filter();
        })->filter();
    }
}

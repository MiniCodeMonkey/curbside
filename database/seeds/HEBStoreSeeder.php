<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\SeedLocations;
use App\Chain;
use App\Store;

class HEBStoreSeeder extends Seeder
{
    public function run() {
        if (Chain::where('name', 'H-E-B')->first()) {
            return;
        }

        $chain = new Chain();
        $chain->name = 'H-E-B';
        $chain->url = 'https://www.heb.com';
        $chain->save();

        $client = new Client([
            'cookies' => true,
            'timeout' => 30
        ]);

        $savedStoreIds = [];

        $seedLocations = SeedLocations::byState('TX');

        foreach ($seedLocations as $index => $location) {
            if ($index % 10 === 0) {
                echo round($index / count($seedLocations) * 100) . '%' . PHP_EOL;
            }

            $searchParameters = [
                'latitude' => (float)$location[0],
                'longitude' => (float)$location[1],
                'curbsideOnly' => true,
                'radius' => 100,
                'nextAvailableTimeslot' => true,
                'includeMedical' => false
            ];

            try {
                $response = $client->post('https://www.heb.com/commerce-api/v1/store/locator/coordinates', [
                    'json' => $searchParameters
                ]);
            } catch (ClientException $e) {
                if ($e->getCode() === 404) {
                    // No results? That's fine, let's just skip to the next one
                    continue;
                }
            }

            $json = json_decode((string)$response->getBody());

            foreach ($json->stores as $item) {
                $storeId = $item->store->id;

                if ($item->store->isCurbside && !in_array($storeId, $savedStoreIds)) {
                    $savedStoreIds[] = $storeId;

                    $store = new Store();
                    $store->name = $item->store->name;
                    $store->identifier = $storeId;
                    $store->street = $item->store->address1;
                    $store->city = $item->store->city;
                    $store->state = $item->store->state;
                    $store->zip = $item->store->postalCode;
                    $store->country = 'US';

                    $store->location = new Point($item->store->latitude, $item->store->longitude);

                    $chain->stores()->save($store);
                }
            }
        }
    }
}

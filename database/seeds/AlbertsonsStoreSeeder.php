<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\SeedLocations;
use App\Geocoder;
use App\Chain;
use App\Store;

class AlbertsonsStoreSeeder extends Seeder
{
    public function run() {
        if (Chain::where('name', 'Albertsons')->first()) {
            return;
        }

        $client = new Client([
            'cookies' => true,
            'timeout' => 30,
            'headers' => [
                'ocp-apim-subscription-key' => '7bad9afbb87043b28519c4443106db06'
            ]
        ]);

        $geocoder = new Geocoder();
        $seedLocations = SeedLocations::zips();
        $savedStoreIds = [];

        foreach ($seedLocations as $index => $zip) {
            if ($index % 10 === 0) {
                echo round($index / count($seedLocations) * 100) . '%' . PHP_EOL;
            }

            $query = [
              'zipcode' => $zip,
              'radius' => 500,
              'size' => 999
            ];

            try {
                $response = $client->get('https://www.albertsons.com/abs/pub/xapi/storeresolver/all', [
                    'query' => $query
                ]);
            } catch (ClientException $e) {
                if ($e->getCode() === 404) {
                    continue;
                }
            }

            $json = json_decode((string)$response->getBody());

            if (!isset($json->pickup->stores)) {
                continue;
            }

            foreach ($json->pickup->stores as $item) {
                if (!in_array($item->locationId, $savedStoreIds)) {
                    $savedStoreIds[] = $item->locationId;

                    $chain = Chain::firstOrCreate([
                        'name' => $item->storeRewards->storeName,
                        'url' => 'https://www.albertsons.com'
                    ]);

                    $store = new Store();
                    $store->name = $item->storeRewards->storeName;
                    $store->identifier = $item->locationId;
                    $store->street = $item->address->line1;
                    $store->city = $item->address->city;
                    $store->state = $item->address->state;
                    $store->zip = $item->address->zipcode;
                    $store->country = $item->address->country;

                    $store->location = $geocoder->geocode(implode(' ', [$store->street, $store->city, $store->state, $store->zip]));

                    if ($store->location) {
                        $chain->stores()->save($store);
                    }
                }
            }
        }
    }
}

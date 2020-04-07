<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Chain;
use App\Store;

class HEBStoreSeeder extends Seeder
{
    // Thank you alltheplaces <3
    const LOCATION_SEED_FILE_URL = 'https://raw.githubusercontent.com/alltheplaces/alltheplaces/master/locations/searchable_points/us_centroids_25mile_radius_state.csv';

    private $locationSeed = [];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->ensureLocationSeedFile();
        $this->seed();
    }

    private function ensureLocationSeedFile() {
        $locationSeedFilename = storage_path('searchable_points_25.csv');

        if (!file_exists($locationSeedFilename)) {
            file_put_contents($locationSeedFilename, file_get_contents(self::LOCATION_SEED_FILE_URL));
        }

        if (($handle = fopen($locationSeedFilename, 'r')) !== FALSE) {
            $lineNo = 1;
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if ($lineNo > 1 && $row[3] === 'TX') {
                    $this->locationSeed[] = [
                        $latitude = $row[1],
                        $longitude = $row[2]
                    ];
                }
                $lineNo++;
            }
            fclose($handle);
        }
    }

    private function seed() {
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

        foreach ($this->locationSeed as $index => $location) {
            if ($index % 10 === 0) {
                echo round($index / count($this->locationSeed) * 100) . '%' . PHP_EOL;
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

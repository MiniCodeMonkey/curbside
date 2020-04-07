<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GuzzleHttp\Client;
use App\Chain;
use App\Store;

class KrogerStoreSeeder extends Seeder
{
    // Thank you alltheplaces <3
    const LOCATION_SEED_FILE_URL = 'https://raw.githubusercontent.com/alltheplaces/alltheplaces/master/locations/searchable_points/us_centroids_100mile_radius.csv';

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
        $locationSeedFilename = storage_path('searchable_points.csv');

        if (!file_exists($locationSeedFilename)) {
            file_put_contents($locationSeedFilename, file_get_contents(self::LOCATION_SEED_FILE_URL));
        }

        if (($handle = fopen($locationSeedFilename, 'r')) !== FALSE) {
            $lineNo = 1;
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if ($lineNo > 1) {
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
        if (Chain::where('name', 'Kroger')->first()) {
            return;
        }

        $bannerNames = [
            "CITYMARKET" => "City Market",
            "DILLONS" => "Dillons",
            "FOODSCO" => "Foods Co",
            "FOOD4LESS" => "Food 4 Less",
            "FREDMEYER" => "Fred Meyer",
            "FRYSFOOD" => "Fry's",
            "GERBES" => "Gerbes",
            "JAYCFOODS" => "JayC Foods Stores",
            "KINGSOOPERS" => "King Soopers",
            "KROGER" => "Kroger",
            "OWENSMARKET" => "Owen's Market",
            "PAY-LESS" => "Pay-Less",
            "PPSRX" => "Postal Prescription Services",
            "QFC" => "QFC",
            "RALPHS" => "Ralphs",
            "SMITHSFOODANDDRUG" => "Smith's",
            "KWIKSHOP" => "Kwik Shop",
            "LOAFNJUG" => "Loaf 'N Jug",
            "QUIKSTOP" => "Quik Stop",
            "TOMT" => "Tom Thumb",
            "TURKEYHILLSTORES" => "Turkey Hill Mini Markets",
            "COPPS" => "Copps",
            "MARIANOS" => "Marianos",
            "PICKNSAVE" => "Pick 'n Save",
            "METROMARKET" => "Metro Market",
            "HARRISTEETER" => "Harris Teeter",
            "RULERFOODS" => "Ruler Foods",
            "THELITTLECLINIC" => "The Little Clinic",
            "HARRISTEETERPHARMACY" => "Harris Teeter Pharmacy",
        ];

        $client = new Client([
            'cookies' => true,
            'timeout' => 30,
            'headers' => [
                'accept' => 'application/json, text/plain, */*',
                'accept-encoding' => 'gzip, deflate, br',
                'content-type' => 'application/json;charset=UTF-8',
                'origin' => 'https://www.kroger.com',
                'referer' => 'https://www.kroger.com/stores/search?searchText=virginia&selectedStoreFilters=Pickup',
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36'
            ]
        ]);

        $query = <<<'GRAPHQL'
query storeGeolocationSearch($latitude: String!, $longitude: String!, $filters: [String]!) {
  storeGeolocationSearch(latitude: $latitude, longitude: $longitude, filters: $filters) {
    stores {
      ...storeSearchResult
    }
  }
}

fragment storeSearchResult on Store {
  banner
  vanityName
  divisionNumber
  storeNumber
  storeType
  latitude
  longitude
  tz
  address {
    addressLine1
    addressLine2
    city
    countryCode
    stateCode
    zip
  }
  fulfillmentMethods{
    hasPickup
    hasDelivery
  }
}
GRAPHQL;

        $savedStoreIds = [];

        foreach ($this->locationSeed as $index => $location) {
            if ($index % 10 === 0) {
                echo round($index / count($this->locationSeed) * 100) . '%' . PHP_EOL;
            }

            $searchParameters = [
              'query' => $query,
              'variables' => [
                'latitude' => $location[0],
                'longitude' => $location[1],
                'filters' => [
                  '94' // Pickup-only
                ]
              ],
              'operationName' => 'storeGeolocationSearch'
            ];

            $client->get('https://www.kroger.com/stores/search');

            $response = $client->post('https://www.kroger.com/stores/api/graphql', [
                'json' => $searchParameters
            ]);

            $json = json_decode((string)$response->getBody());

            foreach ($json->data->storeGeolocationSearch->stores as $item) {
                $storeId = $item->divisionNumber . $item->storeNumber;

                if ($item->fulfillmentMethods->hasPickup && !in_array($storeId, $savedStoreIds)) {
                    $savedStoreIds[] = $storeId;

                    $banner = $bannerNames[$item->banner ?? 'KROGER'] ?? 'Kroger';

                    $chain = Chain::firstOrCreate([
                        'name' => $banner,
                        'url' => 'https://www.kroger.com'
                    ]);

                    $store = new Store();
                    $store->name = $item->vanityName;
                    $store->identifier = $storeId;
                    $store->street = $item->address->addressLine1;
                    $store->city = $item->address->city;
                    $store->state = $item->address->stateCode;
                    $store->zip = $item->address->zip;
                    $store->country = $item->address->countryCode;

                    $store->location = new Point($item->latitude, $item->longitude);

                    $chain->stores()->save($store);
                }
            }
        }
    }
}

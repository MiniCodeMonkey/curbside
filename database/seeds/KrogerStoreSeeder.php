<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GuzzleHttp\Client;
use App\SeedLocations;
use App\Chain;
use App\Store;

class KrogerStoreSeeder extends Seeder
{
    public function run() {
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

        $seedLocations = SeedLocations::countrywide();
        $savedStoreIds = [];

        foreach ($seedLocations as $index => $location) {
            if ($index % 10 === 0) {
                echo round($index / count($seedLocations) * 100) . '%' . PHP_EOL;
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

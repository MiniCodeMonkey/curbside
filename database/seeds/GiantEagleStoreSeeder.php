<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GuzzleHttp\Client;
use App\Chain;
use App\Store;

class GiantEagleStoreSeeder extends Seeder
{
    private function getAllLocationZips() {
        $zips = collect();
        $skip = 0;

        do {
            echo $skip . PHP_EOL;

            $url = 'https://www.gianteagle.com/api/sitecore/locations/getlocationlistvm?f=offeringStoreFeatures/any(a:%20a%20eq%20%27Curbside%20Express%20Pickup%27)&skip=' . $skip;

            $json = json_decode(file_get_contents($url));
            $newLocations = collect($json->Locations ?? [])
                ->map(function ($location) {
                    return substr($location->Address->Zip, 0, 5);
                });

            $zips = $zips->merge($newLocations);

            $skip += $newLocations->count();
        } while ($newLocations->count() > 0);

        return $zips
            ->unique()
            ->values()
            ->toArray();
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Chain::where('name', 'Giant Eagle')->first()) {
            return;
        }

        $chain = new Chain();
        $chain->name = 'Giant Eagle';
        $chain->url = 'https://www.gianteagle.com';
        $chain->save();

        $zips = $this->getAllLocationZips();

        $client = new Client([
            'base_uri' => 'https://adapter.shop.gianteagle.com',
            'cookies' => true
        ]);

        $query = <<<'GRAPHQL'
query StoreChooserQuery(
  $count: Int!
  $cursor: String
  $fulfillmentMethod: FulfillmentMethod!
  $zipcode: String
  ) {
  ...StoreChooser_paginated_ISZZc
}

fragment StoreChooser_paginated_ISZZc on Query {
  paginatedStores(first: $count, after: $cursor, fulfillmentMethod: $fulfillmentMethod, zipcode: $zipcode) {
    edges {
      cursor
      node {
        id
        name
        code
        slug
        address {
          street
          city
          state
          zipcode
          location {
            lat
            lng
          }
          id
        }
        phoneNumber {
          prettyNumber
          id
        }
        services {
          delivery
          pickup
        }
        __typename
      }
    }
    pageInfo {
      endCursor
      hasNextPage
    }
  }
}
GRAPHQL;

        $savedStoreIds = [];

        foreach ($zips as $index => $zip) {
            echo round(($index / count($zips)) * 100) . '%' . PHP_EOL;

            $json = json_decode((string)$client->post('api', [
                'json' => [
                    'query' => $query,
                    'variables' => [
                        'count' => 100,
                        'cursor' => null,
                        'fulfillmentMethod' => 'pickup',
                        'zipcode' => $zip
                    ]
                ]
            ])->getBody());

            collect($json->data->paginatedStores->edges)->each(function ($item) use ($chain, &$savedStoreIds) {

                if (!in_array($item->node->id, $savedStoreIds)) {
                    $savedStoreIds[] = $item->node->id;

                    $store = new Store();
                    $store->name = $item->node->name;
                    $store->identifier = $item->node->slug;
                    $store->street = $item->node->address->street;
                    $store->city = $item->node->address->city;
                    $store->state = $item->node->address->state;
                    $store->zip = $item->node->address->zipcode;
                    $store->country = 'US';

                    $store->location = new Point(
                        $item->node->address->location->lat,
                        $item->node->address->location->lng
                    );

                    $chain->stores()->save($store);
                }
            });
        }
    }
}

<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GuzzleHttp\Client;
use App\Chain;
use App\Store;

class WeisMarketsStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Chain::where('name', 'Weis Markets')->first()) {
            return;
        }

        $chain = new Chain();
        $chain->name = 'Weis Markets';
        $chain->url = 'https://www.weismarkets.com';
        $chain->save();

        $client = new Client([
            'base_uri' => 'https://www.weismarkets.com/api/',
            'cookies' => true
        ]);

        $json = json_decode((string)$client->post('m_user/sessioninit')->getBody());
        $csrfToken = $json[0] ?? null;

        $json = json_decode((string)$client->get('m_store_location', [
            'query' => ['store_type_ids' => '1,2,3'],
            'headers' => ['x-csrf-token' => $csrfToken]
        ])->getBody());

        collect($json->stores)->each(function ($item) use ($chain) {
            $store = new Store();
            $store->name = $item->storeName;
            $store->identifier = $item->locationID;
            $store->street = $item->address;
            $store->city = $item->city;
            $store->state = $item->state;
            $store->zip = $item->zip;
            $store->country = 'US';

            $store->location = new Point($item->latitude, $item->longitude);

            $chain->stores()->save($store);
        });
    }
}

<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Goutte\Client as GoutteClient;
use App\Chain;
use App\Store;

class HarrisTeeterStoreSeeder extends Seeder
{
    public function run() {
        if (Chain::where('name', 'Harris Teeter')->first()) {
            return;
        }

        $chain = new Chain();
        $chain->name = 'Harris Teeter';
        $chain->url = 'https://www.harristeeter.com';
        $chain->save();

        $client = new GoutteClient();
        $client->request('POST', 'https://www.harristeeter.com/api/checkLogin');
        $client->request('GET', 'https://www.harristeeter.com/store/#/app/store-locator');
        $client->request('GET', 'https://www.harristeeter.com/api/v1/stores/search?Address=20003&Radius=1000000&AllStores=true&NewOrdering=false&OnlyPharmacy=false&OnlyFreshFood=false');

        $json = json_decode($client->getResponse()->getContent());

        collect($json->Data)->each(function ($item) use ($chain) {
            $storeId = $item->ExpressLaneStoreID ?? null;

            if ($storeId) {
                $store = new Store();
                $store->name = $item->StoreName;
                $store->identifier = $item->ExpressLaneStoreID;
                $store->street = $item->Street;
                $store->city = $item->City;
                $store->state = $item->State;
                $store->zip = $item->ZipCode;
                $store->country = $item->Country;

                $store->location = new Point($item->Latitude, $item->Longitude);

                $chain->stores()->save($store);
            }
        });
    }
}

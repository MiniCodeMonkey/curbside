<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Goutte\Client;
use App\Chain;
use App\Store;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedWegmans();
        $this->seedHarrisTeeter();
    }

    private function seedWegmans() {
        $chain = new Chain();
        $chain->name = 'Wegmans';
        $chain->url = 'https://www.wegmans.com';
        $chain->save();

        $json = json_decode(file_get_contents('https://shop.wegmans.com/api/v2/stores'));

        collect($json->items)->each(function ($item) use ($chain) {
            $store = new Store();
            $store->name = ucwords(strtolower($item->name));
            $store->identifier = $item->id;
            $store->street = $item->address->address1;
            $store->city = $item->address->city;
            $store->state = $item->address->province;
            $store->zip = $item->address->postal_code;
            $store->country = substr($item->address->country, 0, 2);

            $store->location = new Point($item->location->latitude, $item->location->longitude);

            $chain->stores()->save($store);
        });
    }

    private function seedHarrisTeeter() {
        $chain = new Chain();
        $chain->name = 'Harris Teeter';
        $chain->url = 'https://www.harristeeter.com';
        $chain->save();

        $client = new Client();
        $client->request('POST', 'https://www.harristeeter.com/api/checkLogin');
        $client->request('GET', 'https://www.harristeeter.com/store/#/app/store-locator');

        $crawler = $client->request('GET', 'https://www.harristeeter.com/api/v1/stores/search?Address=20003&Radius=1000000&AllStores=true&NewOrdering=false&OnlyPharmacy=false&OnlyFreshFood=false');

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

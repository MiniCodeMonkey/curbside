<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GuzzleHttp\Client;
use App\Chain;
use App\Store;

class WegmansStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Chain::where('name', 'Wegmans')->first()) {
            return;
        }

        $chain = new Chain();
        $chain->name = 'Wegmans';
        $chain->url = 'https://www.wegmans.com';
        $chain->save();

        $json = json_decode(file_get_contents('https://shop.wegmans.com/api/v2/stores'));

        collect($json->items)->each(function ($item) use ($chain) {
            if ($item->has_pickup) {
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
            }
        });
    }
}

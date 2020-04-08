<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GuzzleHttp\Client;
use App\Chain;
use App\Store;

class PriceChopperStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Chain::where('name', 'Price Chopper')->first()) {
            return;
        }

        $chain = new Chain();
        $chain->name = 'Price Chopper';
        $chain->url = 'https://www.pricechopper.com';
        $chain->save();

        $client = new Client([
            // As of 4/7/2020 Price Chopper has an incomplete SSL chain
            // as verified by ssllabs.com
            // There's nothing high-risk in this request, so we're fine
            // with disabling SSL
            'verify' => false
        ]);

        $json = json_decode((string)$client->get('https://shop.pricechopper.com/api/v2/stores')->getBody());

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

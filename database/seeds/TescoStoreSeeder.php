<?php

use Illuminate\Database\Seeder;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use GuzzleHttp\Client;
use App\Chain;
use App\Store;

class TescoStoreSeeder extends Seeder
{
    const LOCATION_SEED_FILE_URL = 'https://gist.githubusercontent.com/davidpiesse/51c44022cfc67f5a3efb21bb846d9a41/raw/d942622e0181b517188cec5d34b44ec0b50207dc/tesco_stores_click_and_collect.json';

    public function run()
    {
        $this->ensureLocationSeedFile();
        $this->seed();
    }

    private function ensureLocationSeedFile() {
        $locationSeedFilename = storage_path('tesco_stores.json');

        if (!file_exists($locationSeedFilename)) {
            file_put_contents($locationSeedFilename, file_get_contents(self::LOCATION_SEED_FILE_URL));
        }
    }

    public function seed()
    {
        if (Chain::where('name', 'Tesco')->first()) {
            return;
        }

        $chain = new Chain();
        $chain->name = 'Tesco';
        $chain->url = 'https://www.tesco.com';
        $chain->save();

        $json = json_decode(file_get_contents(storage_path('tesco_stores.json')));

        collect($json)->each(function ($item) use ($chain) {
            $store = new Store();
            $store->name = $item->name;
            $store->identifier = $item->identifier;
            $store->street = $item->street;
            $store->city = $item->city;
            $store->state = $item->state;
            $store->zip = $item->zip;
            $store->country = $item->country;

            $store->location = new Point($item->latitude, $item->longitude);

            $chain->stores()->save($store);
        });
    }
}

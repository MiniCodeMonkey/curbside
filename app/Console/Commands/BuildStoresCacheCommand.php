<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Store;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\Point;

class BuildStoresCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:stores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Builds store geojson file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $features = Store::with('chain')
            ->get()
            ->map(function ($store) {
                $properties = [
                    'chain' => $store->chain->name,
                    'color' => $store->chain->getColor(),
                    'name' => $store->name,
                ];

                return new Feature(
                    new Point([$store->location->getLng(), $store->location->getLat()]),
                    $properties,
                    $store->id
                );
            })
            ->toArray();

        $featureCollection = new FeatureCollection($features);

        file_put_contents(public_path('stores.geojson'), json_encode($featureCollection));
    }
}

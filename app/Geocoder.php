<?php

namespace App;

use Grimzy\LaravelMysqlSpatial\Types\Point;

class Geocoder
{
    public function geocode(string $address) {
        $apiKey = config('services.geocodio.api_key');
        $url = 'https://api.geocod.io/v1.4/geocode?q=' . urlencode($address) .'&api_key=' . $apiKey;

        $json = json_decode(file_get_contents($url));

        $firstResult = $json->results[0] ?? null;

        if ($firstResult) {
            return new Point($firstResult->location->lat, $firstResult->location->lng);
        }

        return null;
    }
}

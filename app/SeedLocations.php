<?php

namespace App;

class SeedLocations
{
    // Thank you alltheplaces <3
    const LOCATION_SEED_FILE_URL_100 = 'https://raw.githubusercontent.com/alltheplaces/alltheplaces/master/locations/searchable_points/us_centroids_100mile_radius_state.csv';
    const LOCATION_SEED_FILE_URL_25 = 'https://raw.githubusercontent.com/alltheplaces/alltheplaces/master/locations/searchable_points/us_centroids_25mile_radius_state.csv';

    private function ensureLocationSeedFile(string $filename, string $state = null) {
        $locations = [];

        $locationSeedFilename = storage_path('points/' . basename($filename));

        if (!file_exists($locationSeedFilename)) {
            file_put_contents($locationSeedFilename, file_get_contents($filename));
        }

        if (($handle = fopen($locationSeedFilename, 'r')) !== FALSE) {
            $lineNo = 1;
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if ($lineNo > 1 && (!$state || $row[3] == $state)) {
                    $locations[] = [
                        $latitude = $row[1],
                        $longitude = $row[2]
                    ];
                }
                $lineNo++;
            }
            fclose($handle);
        }

        return $locations;
    }

    public static function byState($state) {
        return self::ensureLocationSeedFile(self::LOCATION_SEED_FILE_URL_25, $state);
    }

    public static function countrywide() {
        return self::ensureLocationSeedFile(self::LOCATION_SEED_FILE_URL_100);
    }

    public static function zips() {
        return array_map('trim', file(storage_path('points/zips')));
    }
}

<?php

namespace App;

use ColorThief\ColorThief;
use Illuminate\Database\Eloquent\Model;
use Str;

class Chain extends Model
{
    protected $fillable = [
        'name',
        'url'
    ];

    public function stores() {
        return $this->hasMany(Store::class);
    }

    public function scannerRuns() {
        return $this->hasMany(ScannerRun::class);
    }

    public function scopeEnabled($query) {
        return $query->where('enabled', true);
    }

    public function getStoreScanner(): ?StoreScanner {
        $providers = [
            'https://www.wegmans.com' => WegmansStoreScanner::class,
            'https://www.harristeeter.com' => HarrisTeeterStoreScanner::class,
            'https://www.kroger.com' => KrogerStoreScanner::class,
            'https://www.heb.com' => HEBStoreScanner::class,
            'https://www.pricechopper.com' => PriceChopperStoreScanner::class,
            'https://www.foodlion.com' => FoodLionStoreScanner::class,
            'https://www.weismarkets.com' => WeisMarketsStoreScanner::class,
            'https://www.albertsons.com' => AlbertsonsStoreScanner::class,
            'https://www.gianteagle.com' => GiantEagleStoreScanner::class,
        ];

        $providerClass = $providers[$this->url] ?? null;

        if ($providerClass) {
            return new $providerClass();
        }

        return null;
    }

    public function getColor() {
        $filename = public_path('img/logos/' . $this->logo_filename);

        if (Str::endsWith($filename, '.svg')) {
            preg_match_all('/#(?:[0-9a-fA-F]{3}){1,2}/', file_get_contents($filename), $matches);
            $matches = collect($matches[0])
                ->filter(function ($match) {
                    return $match != '#fff' && $match != '#000' && $match != '#ffffff' && $match != '#000000';
                });

            return $matches->count() > 0
                ? $matches->first()
                : '#000';
        } else {
            list($r, $g, $b) = ColorThief::getColor($filename);

            return "rgb($r,$g,$b)";
        }
    }

}

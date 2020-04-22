<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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


}

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

    public function getStoreScanner(): ?StoreScanner {
        $providers = [
            'Wegmans' => WegmansStoreScanner::class,
            'Harris Teeter' => HarrisTeeterStoreScanner::class,
        ];

        $providerClass = $providers[$this->name] ?? null;

        if ($providerClass) {
            return new $providerClass();
        }

        return null;
    }
}

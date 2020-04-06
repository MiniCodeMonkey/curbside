<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use App\WegmansStoreScanner;
use App\HarrisTeeterStoreScanner;

class Store extends Model
{
    use SpatialTrait;

    protected $spatialFields = [
        'location'
    ];

    public function chain() {
        return $this->belongsTo(Chain::class);
    }

    public function subscribers() {
        return $this->belongsToMany(Subscriber::class);
    }

    public function timeslots() {
        return $this->hasMany(Timeslot::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

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
        return $this->hasMany(Subscriber::class);
    }
}

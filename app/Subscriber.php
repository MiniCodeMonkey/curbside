<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class Subscriber extends Model
{
    use SpatialTrait;

    protected $spatialFields = [
        'location'
    ];

    public function store() {
        return $this->hasMany(Store::class);
    }
}

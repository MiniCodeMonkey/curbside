<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class Subscriber extends Model
{
    use SpatialTrait;

    protected $fillable = [
        'phone'
    ];

    protected $spatialFields = [
        'location'
    ];

    public function stores() {
        return $this->belongsToMany(Store::class)->withTimestamps();
    }
}

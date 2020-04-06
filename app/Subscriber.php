<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class Subscriber extends Model
{
    use SpatialTrait;
    use Notifiable;

    protected $fillable = [
        'phone'
    ];

    protected $spatialFields = [
        'location'
    ];

    public function routeNotificationForTwilio() {
        return $this->phone;
    }

    public function stores() {
        return $this->belongsToMany(Store::class)->withTimestamps();
    }

    public function scopeActive($query) {
        return $query->where('status', 'ACTIVE');
    }

    public function radiusInMeters() {
        return $this->radius * 1609.344;
    }
}

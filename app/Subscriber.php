<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class Subscriber extends Model
{
    use SpatialTrait;
    use Notifiable;

    const EARTH_RADIUS_MILES = 3958.8;

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

    public function distanceTo(Store $store) {
        $latFrom = deg2rad($this->location->getLat());
        $lonFrom = deg2rad($this->location->getLng());
        $latTo = deg2rad($store->location->getLat());
        $lonTo = deg2rad($store->location->getLng());

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * self::EARTH_RADIUS_MILES;
    }
}

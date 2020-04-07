<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timeslot extends Model
{
    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    protected $fillable = [
        'store_id',
        'date',
        'from',
        'to',
    ];

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public static function latestCreatedAt(bool $asHumanDiff = false) {
        $latestTimeslot = self::orderBy('created_at', 'DESC')->first();

        $latestCreatedAt = $latestTimeslot
            ? $latestTimeslot->created_at
            : null;

        if ($asHumanDiff && $latestCreatedAt) {
            return $latestCreatedAt->shortRelativeToNowDiffForHumans();
        }

        return $latestTimeslot;
    }
}

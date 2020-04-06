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
}

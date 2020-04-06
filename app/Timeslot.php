<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timeslot extends Model
{
    protected $fillable = [
        'store_id',
        'date',
        'from',
        'to',
    ];
}

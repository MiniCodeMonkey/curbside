<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScannerRun extends Model
{
    protected $fillable = [
        'hostname',
        'status',
        'duration_seconds',
        'stores_scanned',
        'timeslots_found',
        'error_message',
    ];
}

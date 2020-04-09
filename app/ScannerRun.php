<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScannerRun extends Model
{
    protected $fillable = [
        'chain_id',
        'hostname',
        'status',
        'duration_seconds',
        'stores_scanned',
        'timeslots_found',
        'error_message',
    ];

    public function getStatusColorAttribute() {
        switch ($this->attributes['status']) {
            case 'ENQUEUED':
                return 'yellow';

            case 'STARTED':
                return 'orange';

            case 'SUCCEEDED':
                return 'green';

            case 'FAILED':
                return 'red';
        }

        return 'gray';
    }
}

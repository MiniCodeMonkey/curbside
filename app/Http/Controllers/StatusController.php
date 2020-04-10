<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subscriber;
use App\Chain;
use App\Store;

class StatusController extends Controller
{
    public function __invoke(Request $request) {
        $chains = Chain::with(['scannerRuns' => function ($query) {
            $query->where('created_at', '>=', now()->subMinutes(15))
                   ->orderBy('created_at', 'ASC');
        }])
        ->orderBy('enabled', 'DESC')
        ->get();

        return view('status', ['chains' => $chains]);
    }
}

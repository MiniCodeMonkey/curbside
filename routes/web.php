<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $chains = App\Chain::enabled()
        ->orderBy('created_at', 'ASC')
        ->get();

    return view('landing', ['chains' => $chains]);
});

Route::get('stores', function () {
    return view('stores');
});

Route::get('status', 'StatusController');

Route::post('subscribe', 'SubscriberController');
Route::post('webhook', 'TwilioWebhookController');

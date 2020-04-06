<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;

abstract class StoreScanner
{
    protected $store;
    protected $client;
    protected $baseUri = null;

    public function __construct(Store $store) {
        $this->store = $store;
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 30,
            'cookies' => true,
            'headers' => [
                'User-Agent' => 'Curb Run/1.0'
            ]
        ]);
    }

    abstract public function scanPickupSlots(): ?Collection;
}

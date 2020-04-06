<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;

abstract class StoreScanner
{
    protected $client;
    protected $baseUri = null;
    protected $sessionEstablished = false;

    public function __construct() {
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 30,
            'cookies' => true,
            'headers' => [
                'User-Agent' => 'Curb Run/1.0'
            ]
        ]);
    }

    protected function prepareSession() {
        // Can optionally be implemented by subclasses
    }

    public function scan(Store $store): ?Collection {
        if (!$this->sessionEstablished) {
            $this->prepareSession();
            $this->sessionEstablished = true;
        }

        return null;
    }
}

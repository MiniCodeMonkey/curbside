<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;

abstract class StoreScanner
{
    protected $client;
    protected $baseUri = null;
    protected $sessionEstablished = false;

    // NOTE: Only disable if you fully understand the implications
    protected $enableSSLVerification = true;

    public function __construct() {
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 15,
            'cookies' => true,
            'headers' => [
                'User-Agent' => 'Curb Run/1.0'
            ],
            'verify' => $this->enableSSLVerification
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

        // To reduce server load for store websites
        sleep(1);

        return null;
    }
}

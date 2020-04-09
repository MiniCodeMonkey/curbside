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
                'User-Agent' => 'Curb Run/1.1 (https://curb.run)'
            ],
            'verify' => $this->enableSSLVerification
        ]);
    }

    protected function prepareSession() {
        // Can optionally be implemented by subclasses
    }

    public function scan(Collection $stores): ?Collection {
        if (!$this->sessionEstablished) {
            $this->prepareSession();
            $this->sessionEstablished = true;
        }

        // To reduce server load for store websites, wait 500ms between requests
        usleep(500000);

        return null;
    }
}

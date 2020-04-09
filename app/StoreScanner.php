<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Collection;

abstract class StoreScanner
{
    protected $client;
    protected $baseUri = null;
    protected $sessionEstablished = false;

    // NOTE: Only disable if you fully understand the implications
    protected $enableSSLVerification = true;

    public function __construct() {
        $handlerStack = HandlerStack::create(new CurlHandler());
        $retryMiddleware = Middleware::retry(function ($retries, Request $request, Response $response = null, RequestException $exception = null) {
            if ($retries >= 3) {
                return false;
            }

            if ($exception instanceof ConnectException) {
                info('Retrying due to ConnectException');
                return true;
            }

            if ($response) {
                if ($response->getStatusCode() >= 500) {
                    info('Retrying due to status code ' . $response->getStatusCode());
                    return true;
                }
            }

            return false;
        });

        $handlerStack->push($retryMiddleware);

        $this->client = new Client([
            'handler' => $handlerStack,
            'base_uri' => $this->baseUri,
            'timeout' => 15,
            'cookies' => true,
            'headers' => [
                'User-Agent' => 'Curb Run/1.1 (https://curb.run)'
            ],
            'verify' => $this->enableSSLVerification,
            'proxy' => config('curbside.proxy', null)
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

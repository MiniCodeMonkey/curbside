<?php

namespace App;

use RuntimeException;
use GuzzleHttp\RedirectMiddleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Store;

class AlbertsonsStoreScanner extends StoreScanner
{
    const OKTA_URI = 'https://albertsons.okta.com/';

    protected $baseUri = 'https://www.albertsons.com/';

    private $bearerToken;

    private function oAuthLogin() {
        $response = $this->client->post(self::OKTA_URI . 'api/v1/authn', [
            'json' => [
                'username' => config('services.albertsons.email'),
                'password' => config('services.albertsons.password')
            ]
        ]);

        $authn = json_decode((string)$response->getBody());

        if ($authn->status !== 'SUCCESS') {
            throw new RuntimeException('Albertsons: Login error ' . json_encode($authn));
        }

        $oauthState = 'hurt-thumb-etna-annie';
        $response = $this->client->get(self::OKTA_URI . 'oauth2/ausp6soxrIyPrm8rS2p6/v1/authorize', [
            'query' => [
                'client_id' => '0oap6ku01XJqIRdl42p6',
                'redirect_uri' => 'https://www.albertsons.com/bin/safeway/unified/sso/authorize',
                'response_type' => 'code',
                'response_mode' => 'query',
                'state' => $oauthState,
                'nonce' => 'wXz8NobOr5at7KuRdhupx2GGNpVhj2xb1GIUU72AdNOu2iWfnubwj6qoReHGP3By',
                'prompt' => 'none',
                'sessionToken' => $authn->sessionToken,
                'scope' => 'openid profile email offline_access used_credentials',
            ],
            'allow_redirects' => ['track_redirects' => true]
        ]);

        $redirectUrls = $response->getHeader(RedirectMiddleware::HISTORY_HEADER);

        if (count($redirectUrls) <= 2) {
            throw new RuntimeException(
                'Albertsons: Login error. Expected several redirects, got: '
                . print_r($redirectUrls, true)
            );
        }
    }

    private function refreshUser() {
        $response = $this->client->post(self::OKTA_URI . 'api/v1/sessions/me/lifecycle/refresh');

        $lifecycleRefresh = json_decode((string)$response->getBody());
        if ($lifecycleRefresh->status !== 'ACTIVE') {
            throw new RuntimeException('Albertsons: Login error ' . json_encode($lifecycleRefresh));
        }
    }

    private function getAuthToken() {
        $response = $this->client->get('bin/safeway/unified/userinfo', [
            'query' => [
                'banner' => 'abertsons'
            ]
        ]);

        $userInfo = json_decode((string)$response->getBody());
        $this->bearerToken = $userInfo->SWY_SHOP_TOKEN;
    }

    protected function prepareSession() {
        $this->oAuthLogin();
        $this->refreshUser();
        $this->getAuthToken();
    }

    private function getHeaders() {
        return [
            'Authorization' => 'Bearer ' . $this->bearerToken,
            'ocp-apim-subscription-key' => '095d5468d6eb4df0a2a522a2e55ac745'
        ];
    }

    public function scan(Collection $stores): ?Collection {
        parent::scan($stores);

        return $stores->flatMap(function (Store $store) {
            $path = 'abs/pub/erums/slotservice/api/v1/slots/xapi/pre-book';

            $response = json_decode((string)$this->client->get($path, [
                'headers' => $this->getHeaders(),
                'query' => [
                    'storeId' => $store->identifier,
                    'fulfillmentType' => 'DUG',
                ],
            ])->getBody());

            $storeTimezone = $response->storeTimeZone->timeZoneId;

            return collect($response->availableSlots)
                ->flatMap(function ($item) use ($store, $storeTimezone) {
                    $item = (array)$item;
                    $item = array_shift($item);

                    return collect($item->slots)->map(function ($slot) use ($store, $storeTimezone) {
                        $startTime = Carbon::parse($slot->slotStartTS, 'UTC');
                        $startTime->setTimezone($storeTimezone);

                        $endTime = Carbon::parse($slot->slotEndTS, 'UTC');
                        $endTime->setTimezone($storeTimezone);

                        return Timeslot::updateOrCreate([
                            'store_id' => $store->id,
                            'date' => $startTime->format('Y-m-d'),
                            'from' => $startTime,
                            'to' => $endTime,
                        ]);
                    });
                })
                ->filter();
        });
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Chain;

class StoreScannerTest extends TestCase
{
    public function testGetPickupSlots()
    {
        Chain::orderBy('created_at', 'DESC')->get()->each(function (Chain $chain) {
            $store = $chain->stores()->first();
            $storeScanner = $chain->getStoreScanner();

            $slots = $storeScanner->scan($store);
            $this->assertNotNull($slots);
        });
    }
}

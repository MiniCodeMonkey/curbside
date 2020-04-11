<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Chain;

class StoreScannerTest extends TestCase
{
    public function testGetPickupSlots()
    {
        Chain::orderBy('created_at', 'DESC')->get()->each(function (Chain $chain) {
            $stores = $chain->stores()->take(2)->get();
            $storeScanner = $chain->getStoreScanner();

            $this->assertNotNull($storeScanner, 'StoreScanner class should not be null for ' . $chain->name);

            $slots = $storeScanner->scan($stores);
            $this->assertNotNull($slots);
        });
    }
}

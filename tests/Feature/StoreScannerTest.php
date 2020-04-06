<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Chain;

class StoreScannerTest extends TestCase
{
    public function testGetPickupSlots()
    {
        Chain::all()->each(function (Chain $chain) {
            $store = $chain->stores()->first();
            $slots = $store->scanPickupSlots();
            $this->assertNotNull($slots);
        });
    }
}

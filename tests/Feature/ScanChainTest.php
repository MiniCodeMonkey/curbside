<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\ScanChain;
use App\Chain;

class ScanChainTest extends TestCase
{
    public function testScanChain()
    {
        $chain = Chain::first();
        $scanChain = new ScanChain($chain);
        $scanChain->handle();

        $this->assertEquals('SUCCEEDED', $scanChain->scannerRun->status);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ScanChain;
use App\Chain;

class ScanStoresCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans for pickup slots at subscribed stores';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Scan chains in parallel
        Chain::all()->each(function (Chain $chain) {
            dispatch(new ScanChain($chain));
        });
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Subscriber;
use App\Store;

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
        $stores = Store::has('subscribers')
            ->where('subscribers.status', 'ACTIVE')
            ->get();

        dd($stores);
    }
}

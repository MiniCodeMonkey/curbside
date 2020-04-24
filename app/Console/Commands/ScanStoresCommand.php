<?php

namespace App\Console\Commands;

use InvalidArgumentException;
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
    protected $signature = 'scan {--chain=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans for pickup slots at subscribed stores';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $chainName = $this->option('chain');

        if ($chainName) {
            $chain = Chain::where('name', $chainName)->first();

            if (!$chain) {
                throw new InvalidArgumentException('Could not find any chains named "' . $chainName . '"');
            }

            dispatch(new ScanChain($chain));
        } else {
            // Scan chains in parallel
            Chain::where('autoscan', true)->get()->each(function (Chain $chain) {
                dispatch(new ScanChain($chain));
            });
        }
    }
}

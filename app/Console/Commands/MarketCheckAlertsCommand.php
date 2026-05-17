<?php

namespace App\Console\Commands;

use App\Jobs\CheckAlertsJob;
use Illuminate\Console\Command;

class MarketCheckAlertsCommand extends Command
{
    protected $signature   = 'market:alerts:check';
    protected $description = 'Check active price alerts and notify users';

    public function handle(): int
    {
        $this->info('Dispatching alert check job...');
        CheckAlertsJob::dispatchSync();
        $this->info('Alert check completed.');
        return self::SUCCESS;
    }
}

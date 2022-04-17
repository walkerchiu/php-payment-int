<?php

namespace WalkerChiu\Payment\Console\Commands;

use WalkerChiu\Core\Console\Commands\Cleaner;

class PaymentCleaner extends Cleaner
{
    /**
     * The name and signature of the console command.
     *
     * @var String
     */
    protected $signature = 'command:PaymentCleaner';

    /**
     * The console command description.
     *
     * @var String
     */
    protected $description = 'Truncate tables';

    /**
     * Execute the console command.
     *
     * @return Mixed
     */
    public function handle()
    {
        parent::clean('payment');
    }
}

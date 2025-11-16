<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Purchase;

class UpdateUnpaidPurchases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchases:update-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all unpaid purchases to paid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Do you really want to update all unpaid purchases to paid? This action cannot be undone.')) {
            $unpaidPurchases = Purchase::where('payment_status', 'unpaid')->get();
            $count = $unpaidPurchases->count();

            if ($count === 0) {
                $this->info('No unpaid purchases found.');
                return;
            }

            $unpaidPurchases->each->update(['payment_status' => 'paid']);

            $this->info("Successfully updated {$count} unpaid purchases to paid.");
        } else {
            $this->info('Operation cancelled.');
        }
    }
}

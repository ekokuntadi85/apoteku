<?php

namespace App\Console\Commands;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Console\Command;

class UpdatePurchaseStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase:update-status {supplier_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the payment status of all purchases from a specific supplier to \'paid\'';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $supplierId = $this->argument('supplier_id');

        $supplier = Supplier::find($supplierId);

        if (!$supplier) {
            $this->error('Supplier not found.');
            return;
        }

        $updatedCount = Purchase::where('supplier_id', $supplierId)
            ->where('payment_status', 'unpaid')
            ->update(['payment_status' => 'paid']);

        if ($updatedCount > 0) {
            $this->info($updatedCount . ' purchases from supplier ' . $supplier->name . ' have been updated to \'paid\'.');
        } else {
            $this->info('No unpaid purchases found for supplier ' . $supplier->name . '.');
        }
    }
}

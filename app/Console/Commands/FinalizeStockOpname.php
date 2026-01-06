<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockOpname;
use App\Services\StockOpnameService;

class FinalizeStockOpname extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opname:finalize {id : Stock Opname ID to finalize}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finalize stock opname and create journal entry for adjustments';

    /**
     * Execute the console command.
     */
    public function handle(StockOpnameService $service)
    {
        $opnameId = $this->argument('id');

        $this->info("Finalizing Stock Opname #{$opnameId}...");

        $opname = StockOpname::with(['details.productBatch'])->find($opnameId);

        if (!$opname) {
            $this->error("Stock Opname #{$opnameId} not found!");
            return 1;
        }

        if ($opname->isFinalized()) {
            $this->warn("Stock Opname #{$opnameId} is already finalized!");
            return 0;
        }

        // Show summary before finalization
        $this->table(
            ['Info', 'Value'],
            [
                ['Opname Date', $opname->opname_date->format('d/m/Y')],
                ['Notes', $opname->notes ?? '-'],
                ['Details Count', $opname->details->count()],
                ['Status', $opname->status],
            ]
        );

        // Calculate adjustment value
        $totalAdjustmentValue = 0;
        foreach ($opname->details as $detail) {
            $totalAdjustmentValue += $detail->difference * $detail->productBatch->purchase_price;
        }

        $this->line('');
        $this->line('Adjustment Value: Rp ' . number_format($totalAdjustmentValue, 0, ',', '.'));
        
        if ($totalAdjustmentValue < 0) {
            $this->error('Type: LOSS (akan create journal expense)');
        } elseif ($totalAdjustmentValue > 0) {
            $this->info('Type: GAIN (akan create journal income)');
        } else {
            $this->warn('Type: NO ADJUSTMENT (tidak ada selisih)');
        }

        $this->line('');

        try {
            $service->finalizeOpname($opname);
            
            $this->info("✅ Stock Opname #{$opnameId} finalized successfully!");
            
            if ($opname->journal_entry_id) {
                $this->line("Journal Entry ID: {$opname->journal_entry_id}");
                $this->line("Reference: OP-{$opname->id}");
            } else {
                $this->line("No journal entry created (no adjustment needed)");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to finalize: {$e->getMessage()}");
            return 1;
        }
    }
}

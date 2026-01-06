<?php

namespace App\Services;

use App\Models\StockOpname;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockOpnameService
{
    /**
     * Finalize stock opname and create journal entry for adjustments
     * 
     * @param StockOpname $opname
     * @return void
     * @throws \Exception
     */
    public function finalizeOpname(StockOpname $opname): void
    {
        if ($opname->isFinalized()) {
            throw new \Exception('Stock opname sudah difinalisasi sebelumnya.');
        }

        // CRITICAL: Validate all items have purchase price
        $itemsWithoutPrice = [];
        foreach ($opname->details as $detail) {
            if (!$detail->productBatch->purchase_price || $detail->productBatch->purchase_price <= 0) {
                $itemsWithoutPrice[] = $detail->productBatch->product->name . ' (Batch: ' . $detail->productBatch->batch_number . ')';
            }
        }

        if (count($itemsWithoutPrice) > 0) {
            $errorMsg = "Tidak dapat finalisasi! Item berikut tidak memiliki harga beli:\n\n";
            $errorMsg .= "• " . implode("\n• ", $itemsWithoutPrice);
            $errorMsg .= "\n\nSilakan update harga beli terlebih dahulu di master product batch.";
            throw new \Exception($errorMsg);
        }

        DB::beginTransaction();

        try {
            // 1. Calculate total adjustment value
            $totalAdjustmentValue = 0;
            
            foreach ($opname->details as $detail) {
                $differenceQty = $detail->difference; // positive = gain, negative = loss
                $purchasePrice = $detail->productBatch->purchase_price;
                $adjustmentValue = $differenceQty * $purchasePrice;
                
                $totalAdjustmentValue += $adjustmentValue;
            }

            Log::info("Finalizing stock opname #{$opname->id}", [
                'total_adjustment_value' => $totalAdjustmentValue,
                'details_count' => $opname->details->count()
            ]);

            // 2. Create journal entry if there's any adjustment
            if ($totalAdjustmentValue != 0) {
                $journal = $this->createJournalEntry($opname, $totalAdjustmentValue);
                $opname->journal_entry_id = $journal->id;
            }

            // 3. Update opname status
            $opname->status = 'finalized';
            $opname->save();

            DB::commit();

            Log::info("Stock opname #{$opname->id} finalized successfully", [
                'journal_id' => $opname->journal_entry_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to finalize stock opname #{$opname->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Create journal entry for stock adjustment
     * 
     * @param StockOpname $opname
     * @param float $totalAdjustmentValue
     * @return JournalEntry
     */
    private function createJournalEntry(StockOpname $opname, float $totalAdjustmentValue): JournalEntry
    {
        // Get required accounts
        $inventoryAccount = Account::where('code', '104')->first();
        
        if (!$inventoryAccount) {
            throw new \Exception('Akun Persediaan Obat (104) tidak ditemukan.');
        }

        $isLoss = $totalAdjustmentValue < 0;
        $absoluteValue = abs($totalAdjustmentValue);

        // Create journal entry
        $journal = JournalEntry::create([
            'transaction_date' => $opname->opname_date,
            'reference_number' => 'OP-' . $opname->id,
            'description' => 'Penyesuaian Stock Opname - ' . ($opname->notes ?? 'Opname #' . $opname->id),
            'total_amount' => $absoluteValue,
        ]);

        if ($isLoss) {
            // Loss: Expense account debited, Inventory credited
            $expenseAccount = Account::where('code', '502')->first();
            if (!$expenseAccount) {
                throw new \Exception('Akun Biaya Selisih Stock (502) tidak ditemukan. Silakan buat akun tersebut terlebih dahulu.');
            }

            // Debit: Expense
            JournalDetail::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $expenseAccount->id,
                'debit' => $absoluteValue,
                'credit' => 0,
                'memo' => 'Kerugian stock opname',
            ]);

            // Credit: Inventory
            JournalDetail::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $inventoryAccount->id,
                'debit' => 0,
                'credit' => $absoluteValue,
                'memo' => 'Pengurangan persediaan',
            ]);

        } else {
            // Gain: Inventory debited, Income credited
            $incomeAccount = Account::where('code', '801')->first(); // Other income
            if (!$incomeAccount) {
                // Try to create the account if it doesn't exist
                $incomeAccount = Account::create([
                    'code' => '801',
                    'name' => 'Pendapatan Lain-lain',
                    'type' => 'revenue',
                    'normal_balance' => 'credit',
                ]);
            }

            // Debit: Inventory
            JournalDetail::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $inventoryAccount->id,
                'debit' => $absoluteValue,
                'credit' => 0,
                'memo' => 'Penambahan persediaan',
            ]);

            // Credit: Other Income
            JournalDetail::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $incomeAccount->id,
                'debit' => 0,
                'credit' => $absoluteValue,
                'memo' => 'Keuntungan stock opname',
            ]);
        }

        return $journal;
    }
}

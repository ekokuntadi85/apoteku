<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalDetail;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalService
{
    /**
     * Create a journal entry automatically.
     * 
     * @param string $date (Y-m-d)
     * @param string $reference
     * @param string $description
     * @param array $debits [['account_code' => '101', 'amount' => 1000], ...]
     * @param array $credits [['account_code' => '401', 'amount' => 1000], ...]
     */
    public static function createEntry($date, $reference, $description, $debits, $credits)
    {
        return DB::transaction(function () use ($date, $reference, $description, $debits, $credits) {
            $totalDebit = collect($debits)->sum('amount');
            $totalCredit = collect($credits)->sum('amount');

            // Use small tolerance for floating point comparison (0.01 = 1 cent)
            if (abs($totalDebit - $totalCredit) > 0.01) {
                $message = "Journal Entry Imbalanced: $reference. Debit: $totalDebit, Credit: $totalCredit";
                Log::error($message, [
                    'debits' => $debits,
                    'credits' => $credits,
                    'difference' => abs($totalDebit - $totalCredit)
                ]);
                throw new \Exception($message); // Throw exception to trigger rollback
            }

            $entry = JournalEntry::create([
                'transaction_date' => $date,
                'reference_number' => $reference,
                'description' => $description,
                'total_amount' => $totalDebit,
            ]);

            // Process Debits
            foreach ($debits as $item) {
                $account = Account::where('code', $item['account_code'])->first();
                if ($account) {
                    JournalDetail::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $account->id,
                        'debit' => $item['amount'],
                        'credit' => 0,
                    ]);
                } else {
                     Log::warning("Account code not found: {$item['account_code']} for Debit in $reference");
                }
            }

            // Process Credits
            foreach ($credits as $item) {
                $account = Account::where('code', $item['account_code'])->first();
                if ($account) {
                    JournalDetail::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $account->id,
                        'debit' => 0,
                        'credit' => $item['amount'],
                    ]);
                } else {
                    Log::warning("Account code not found: {$item['account_code']} for Credit in $reference");
                }
            }
        });
    }
}

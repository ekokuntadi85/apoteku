<?php

namespace App\Livewire\FinancialReports;

use Livewire\Component;
use App\Models\Account;
use App\Models\JournalDetail;
use Carbon\Carbon;
use Livewire\Attributes\Title;

#[Title('Buku Besar')]
class GeneralLedger extends Component
{
    public $startDate;
    public $endDate;
    public $accountId;
    public $showDetail = false; // Default to summary view

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        // Default to the first account (usually Cash/Kas)
        $firstAccount = Account::where('is_active', true)->orderBy('code')->first();
        $this->accountId = $firstAccount ? $firstAccount->id : null;
    }
    
    public function printView()
    {
        return redirect()->route('reports.finance.general-ledger.print', [
            'accountId' => $this->accountId,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'showDetail' => $this->showDetail ? '1' : '0'
        ]);
    }

    private function groupByDay($details, $isCreditNormal)
    {
        $grouped = [];
        
        foreach ($details as $detail) {
            $date = $detail->journalEntry->transaction_date;
            $ref = $detail->journalEntry->reference_number;
            
            // Detect transaction type from reference prefix
            $type = 'OTHER';
            $typeLabel = 'Transaksi Lain-lain';
            
            if (str_starts_with($ref, 'INV-')) {
                $type = 'SALES';
                $typeLabel = 'Penjualan';
            } elseif (str_starts_with($ref, 'COGS-')) {
                $type = 'COGS';
                $typeLabel = 'HPP';
            } elseif (str_starts_with($ref, 'PUR-')) {
                $type = 'PURCHASE';
                $typeLabel = 'Pembelian';
            } elseif (str_starts_with($ref, 'PAY-')) {
                $type = 'PAYMENT';
                $typeLabel = 'Pembayaran';
            } elseif (str_starts_with($ref, 'EXP-')) {
                $type = 'EXPENSE';
                $typeLabel = 'Pengeluaran';
            }
            
            // Create unique key for grouping: Date + Type
            // Exception: Don't group "OTHER" types, keep them separate
            if ($type === 'OTHER') {
                $key = $date . '|' . $ref; // Unique per transaction
            } else {
                $key = $date . '|' . $type;
            }
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'date' => $date,
                    'type' => $type,
                    'typeLabel' => $typeLabel,
                    'references' => [],
                    'debit' => 0,
                    'credit' => 0,
                    'count' => 0,
                ];
            }
            
            $grouped[$key]['references'][] = $ref;
            $grouped[$key]['debit'] += $detail->debit;
            $grouped[$key]['credit'] += $detail->credit;
            $grouped[$key]['count']++;
        }
        
        // Calculate running balance for grouped entries
        $result = [];
        $runningBalance = 0; // Will be set from outside
        
        foreach ($grouped as $item) {
            if ($isCreditNormal) {
                $runningBalance += ($item['credit'] - $item['debit']);
            } else {
                $runningBalance += ($item['debit'] - $item['credit']);
            }
            
            $result[] = array_merge($item, ['balance' => $runningBalance]);
        }
        
        return $result;
    }

    public function render()
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        
        $ledgerData = [];
        $openingBalance = 0;
        $totalDebit = 0;
        $totalCredit = 0;
        $selectedAccount = null;

        if ($this->accountId) {
            $selectedAccount = Account::find($this->accountId);
            
            // 1. Calculate Opening Balance (Before Start Date)
            // For Asset/Expense: Debit is (+), Credit is (-)
            // For Liability/Equity/Revenue: Credit is (+), Debit is (-)
            // BUT standard ledger often shows Balance in a generic way, usually based on Normal Balance type.
            // Let's stick to: Balance = Debit - Credit (Positive means Debit balance, Negative means Credit balance)
            
            $preDetails = JournalDetail::where('account_id', $this->accountId)
                ->whereHas('journalEntry', function($q) {
                    $q->where('transaction_date', '<', $this->startDate);
                })->get();
            
            $openingDebit = $preDetails->sum('debit');
            $openingCredit = $preDetails->sum('credit');
            
            // Determine "Normal Balance" calculation
            $isCreditNormal = in_array($selectedAccount->type, ['liability', 'equity', 'revenue']);
            
            if ($isCreditNormal) {
                $openingBalance = $openingCredit - $openingDebit;
            } else {
                $openingBalance = $openingDebit - $openingCredit;
            }

            // 2. Fetch Period Transactions
            $details = JournalDetail::with('journalEntry')
                ->where('account_id', $this->accountId)
                ->whereHas('journalEntry', function($q) {
                    $q->whereBetween('transaction_date', [$this->startDate, $this->endDate]);
                })
                ->orderBy(
                    \App\Models\JournalEntry::select('transaction_date')
                        ->whereColumn('journal_entries.id', 'journal_details.journal_entry_id')
                )
                ->orderBy('id') // Secondary sort for stability
                ->get();

            // 3. Process Running Balance
            if ($this->showDetail) {
                // DETAIL MODE: Show every transaction
                $runningBalance = $openingBalance;
                
                foreach ($details as $detail) {
                    $debit = $detail->debit;
                    $credit = $detail->credit;
                    
                    if ($isCreditNormal) {
                        $runningBalance += ($credit - $debit);
                    } else {
                        $runningBalance += ($debit - $credit);
                    }
                    
                    $ledgerData[] = [
                        'date' => $detail->journalEntry->transaction_date,
                        'reference' => $detail->journalEntry->reference_number,
                        'description' => $detail->journalEntry->description,
                        'debit' => $debit,
                        'credit' => $credit,
                        'balance' => $runningBalance,
                        'count' => 1 // Single transaction
                    ];

                    $totalDebit += $debit;
                    $totalCredit += $credit;
                }
            } else {
                // SUMMARY MODE: Group by day and type
                $grouped = [];
                
                foreach ($details as $detail) {
                    $date = $detail->journalEntry->transaction_date;
                    $ref = $detail->journalEntry->reference_number;
                    
                    // Detect transaction type
                    $type = 'OTHER';
                    $typeLabel = 'Transaksi Lain-lain';
                    
                    if (str_starts_with($ref, 'INV-')) {
                        $type = 'SALES';
                        $typeLabel = 'Penjualan';
                    } elseif (str_starts_with($ref, 'COGS-')) {
                        $type = 'COGS';
                        $typeLabel = 'HPP';
                    } elseif (str_starts_with($ref, 'PUR-')) {
                        $type = 'PURCHASE';
                        $typeLabel = 'Pembelian';
                    } elseif (str_starts_with($ref, 'PAY-')) {
                        $type = 'PAYMENT';
                        $typeLabel = 'Pembayaran';
                    } elseif (str_starts_with($ref, 'EXP-')) {
                        $type = 'EXPENSE';
                        $typeLabel = 'Pengeluaran';
                    }
                    
                    // Group key
                    $key = ($type === 'OTHER') ? $date . '|' . $ref : $date . '|' . $type;
                    
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [
                            'date' => $date,
                            'type' => $type,
                            'typeLabel' => $typeLabel,
                            'references' => [],
                            'debit' => 0,
                            'credit' => 0,
                            'count' => 0,
                        ];
                    }
                    
                    $grouped[$key]['references'][] = $ref;
                    $grouped[$key]['debit'] += $detail->debit;
                    $grouped[$key]['credit'] += $detail->credit;
                    $grouped[$key]['count']++;
                    
                    $totalDebit += $detail->debit;
                    $totalCredit += $detail->credit;
                }
                
                // Calculate running balance for grouped entries
                $runningBalance = $openingBalance;
                
                foreach ($grouped as $item) {
                    if ($isCreditNormal) {
                        $runningBalance += ($item['credit'] - $item['debit']);
                    } else {
                        $runningBalance += ($item['debit'] - $item['credit']);
                    }
                    
                    // Format description
                    $description = $item['typeLabel'];
                    if ($item['count'] > 1) {
                        $description .= ' (' . $item['count'] . ' transaksi)';
                    }
                    
                    $ledgerData[] = [
                        'date' => $item['date'],
                        'reference' => $item['type'] . '-DAILY',
                        'description' => $description,
                        'debit' => $item['debit'],
                        'credit' => $item['credit'],
                        'balance' => $runningBalance,
                        'count' => $item['count']
                    ];
                }
            }
        }

        return view('livewire.financial-reports.general-ledger', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'ledgerData' => $ledgerData,
            'openingBalance' => $openingBalance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
        ]);
    }
}

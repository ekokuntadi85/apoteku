<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialReportPrintController extends Controller
{
    public function generalLedger(Request $request)
    {
        $accountId = $request->get('accountId');
        $startDate = $request->get('startDate', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('endDate', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $showDetail = $request->get('showDetail', '0') === '1';
        
        $ledgerData = [];
        $openingBalance = 0;
        $totalDebit = 0;
        $totalCredit = 0;
        $selectedAccount = null;

        if ($accountId) {
            $selectedAccount = Account::find($accountId);
            
            if ($selectedAccount) {
                // Calculate Opening Balance
                $preDetails = JournalDetail::where('account_id', $accountId)
                    ->whereHas('journalEntry', function($q) use ($startDate) {
                        $q->where('transaction_date', '<', $startDate);
                    })->get();
                
                $openingDebit = $preDetails->sum('debit');
                $openingCredit = $preDetails->sum('credit');
                
                $isCreditNormal = in_array($selectedAccount->type, ['liability', 'equity', 'revenue']);
                
                if ($isCreditNormal) {
                    $openingBalance = $openingCredit - $openingDebit;
                } else {
                    $openingBalance = $openingDebit - $openingCredit;
                }

                // Fetch Period Transactions
                $details = JournalDetail::with('journalEntry')
                    ->where('account_id', $accountId)
                    ->whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('transaction_date', [$startDate, $endDate]);
                    })
                    ->orderBy(
                        \App\Models\JournalEntry::select('transaction_date')
                            ->whereColumn('journal_entries.id', 'journal_details.journal_entry_id')
                    )
                    ->orderBy('id')
                    ->get();

                // Process based on mode
                if ($showDetail) {
                    // DETAIL MODE
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
                            'balance' => $runningBalance
                        ];

                        $totalDebit += $debit;
                        $totalCredit += $credit;
                    }
                } else {
                    // SUMMARY MODE
                    $grouped = [];
                    
                    foreach ($details as $detail) {
                        $date = $detail->journalEntry->transaction_date;
                        $ref = $detail->journalEntry->reference_number;
                        
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
                        
                        $key = ($type === 'OTHER') ? $date . '|' . $ref : $date . '|' . $type;
                        
                        if (!isset($grouped[$key])) {
                            $grouped[$key] = [
                                'date' => $date,
                                'type' => $type,
                                'typeLabel' => $typeLabel,
                                'debit' => 0,
                                'credit' => 0,
                                'count' => 0,
                            ];
                        }
                        
                        $grouped[$key]['debit'] += $detail->debit;
                        $grouped[$key]['credit'] += $detail->credit;
                        $grouped[$key]['count']++;
                        
                        $totalDebit += $detail->debit;
                        $totalCredit += $detail->credit;
                    }
                    
                    // Calculate running balance
                    $runningBalance = $openingBalance;
                    
                    foreach ($grouped as $item) {
                        if ($isCreditNormal) {
                            $runningBalance += ($item['credit'] - $item['debit']);
                        } else {
                            $runningBalance += ($item['debit'] - $item['credit']);
                        }
                        
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
                            'balance' => $runningBalance
                        ];
                    }
                }
            }
        }

        return view('livewire.financial-reports.general-ledger-print', [
            'selectedAccount' => $selectedAccount,
            'ledgerData' => $ledgerData,
            'openingBalance' => $openingBalance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'showDetail' => $showDetail,
        ]);
    }
}

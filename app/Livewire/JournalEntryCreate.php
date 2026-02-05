<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Title('Input Jurnal Umum')]
class JournalEntryCreate extends Component
{
    public $transaction_date;
    public $reference_number;
    public $description;
    
    public $rows = [];
    public $accounts = [];

    public function mount()
    {
        $this->transaction_date = date('Y-m-d');
        $this->accounts = Account::where('is_active', true)->orderBy('code')->get();
        
        // Initialize with 2 empty rows
        $this->addRow();
        $this->addRow();
    }

    public function addRow()
    {
        $this->rows[] = [
            'account_id' => '',
            'debit' => 0,
            'credit' => 0,
            'memo' => '',
        ];
    }

    public function removeRow($index)
    {
        if (count($this->rows) > 2) {
            unset($this->rows[$index]);
            $this->rows = array_values($this->rows);
        }
    }

    public function getTotalDebitProperty()
    {
        return collect($this->rows)->sum('debit');
    }

    public function getTotalCreditProperty()
    {
        return collect($this->rows)->sum('credit');
    }

    public function save()
    {
        $this->validate([
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'rows.*.account_id' => 'required|exists:accounts,id',
            'rows.*.debit' => 'numeric|min:0',
            'rows.*.credit' => 'numeric|min:0',
        ]);

        if (abs($this->totalDebit - $this->totalCredit) > 0.01) {
            session()->flash('error', 'Debit dan Kredit tidak seimbang!');
            return;
        }

        if ($this->totalDebit <= 0) {
            session()->flash('error', 'Total transaksi tidak boleh 0!');
            return;
        }

        DB::beginTransaction();
        try {
            $journalEntry = JournalEntry::create([
                'transaction_date' => $this->transaction_date,
                'reference_number' => $this->reference_number,
                'description' => $this->description,
                'total_amount' => $this->totalDebit, // Use total Debit as the transaction amount
            ]);

            foreach ($this->rows as $row) {
                // Skip empty rows if user left them 0/0 (though validation catches this usually)
                if ($row['debit'] == 0 && $row['credit'] == 0) continue;

                JournalDetail::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $row['account_id'],
                    'debit' => $row['debit'],
                    'credit' => $row['credit'],
                    'memo' => $row['memo'],
                ]);
            }

            DB::commit();

            // Reset form
            $this->reset(['description', 'reference_number']);
            $this->transaction_date = date('Y-m-d');
            $this->rows = [];
            $this->addRow();
            $this->addRow();
            
            session()->flash('message', 'Jurnal berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan jurnal: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.journal-entry-create');
    }
}

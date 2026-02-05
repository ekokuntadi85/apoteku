<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

use Livewire\Attributes\Title;

#[Title('Lihat Transaksi')]
class TransactionShow extends Component
{
    public $transaction;

    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction->load(['user', 'customer', 'transactionDetails.product']);
    }

    public function deleteTransaction()
    {
        DB::transaction(function () {
            // Load the transactionDetailBatches relationship before deleting
            $this->transaction->load('transactionDetails.transactionDetailBatches');

            // Loop through details to trigger model events for stock management
            foreach ($this->transaction->transactionDetails as $detail) {
                $detail->delete();
            }
            // Now delete the transaction itself
            $this->transaction->delete();
        });

        session()->flash('message', 'Transaksi berhasil dihapus.');
        return redirect()->route('transactions.index');
    }

    public function render()
    {
        return view('livewire.transaction-show');
    }
}
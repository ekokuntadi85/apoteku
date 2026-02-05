<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('Catat Pembayaran')]
class PaymentCreate extends Component
{
    public $transaction;
    public $amount;
    public $payment_method = 'cash';
    public $notes;
    public $payment_date;
    
    public $remaining_amount = 0;
    
    protected $rules = [
        'amount' => 'required|numeric|min:0.01',
        'payment_method' => 'required|in:cash,transfer,giro,other',
        'payment_date' => 'required|date|before_or_equal:today',
        'notes' => 'nullable|string|max:500',
    ];
    
    protected $messages = [
        'amount.required' => 'Jumlah pembayaran wajib diisi.',
        'amount.numeric' => 'Jumlah pembayaran harus berupa angka.',
        'amount.min' => 'Jumlah pembayaran minimal Rp 0.01.',
        'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
        'payment_date.before_or_equal' => 'Tanggal pembayaran tidak boleh di masa depan.',
    ];
    
    public function mount($transaction)
    {
        $this->transaction = Transaction::with('customer', 'payments')->findOrFail($transaction);
        $this->payment_date = now()->format('Y-m-d');
        $this->remaining_amount = $this->transaction->grand_total - $this->transaction->amount_paid;
        $this->amount = $this->remaining_amount;
    }
    
    public function updatedAmount()
    {
        // Validate amount doesn't exceed remaining
        if ($this->amount > $this->remaining_amount) {
            $this->amount = $this->remaining_amount;
        }
    }
    
    public function savePayment()
    {
        // Custom validation for amount
        $this->validate();
        
        if ($this->amount > $this->remaining_amount) {
            $this->addError('amount', 'Jumlah pembayaran melebihi sisa tagihan (Rp ' . number_format($this->remaining_amount, 0) . ')');
            return;
        }
        
        Payment::create([
            'transaction_id' => $this->transaction->id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'payment_date' => $this->payment_date,
            'user_id' => Auth::id(),
        ]);
        
        session()->flash('message', 'Pembayaran berhasil dicatat sebesar Rp ' . number_format($this->amount, 0));
        return redirect()->route('accounts-receivable.index');
    }
    
    public function render()
    {
        return view('livewire.payment-create');
    }
}

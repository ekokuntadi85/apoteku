<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YearEndClosing extends Model
{
    protected $fillable = [
        'closing_year',
        'closed_at',
        'closed_by',
        'opening_purchase_id',
        'total_transactions_deleted',
        'total_purchases_deleted',
        'total_journal_entries_deleted',
        'total_expenses_deleted',
        'total_payments_deleted',
        'total_stock_movements_deleted',
        'backup_file_path',
        'status',
        'error_message',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'closing_year' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function openingPurchase()
    {
        return $this->belongsTo(Purchase::class, 'opening_purchase_id');
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}

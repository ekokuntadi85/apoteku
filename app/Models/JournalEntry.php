<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'transaction_date',
        'reference_number',
        'description',
        'total_amount',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function details()
    {
        return $this->hasMany(JournalDetail::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = ['opname_date', 'notes', 'user_id', 'is_migrated', 'status', 'journal_entry_id'];

    protected $casts = [
        'opname_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(StockOpnameDetail::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(\App\Models\JournalEntry::class);
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}

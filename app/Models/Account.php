<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type', // asset, liability, equity, revenue, expense
        'description',
        'is_active',
    ];

    public function journalDetails()
    {
        return $this->hasMany(JournalDetail::class);
    }
}

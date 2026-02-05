<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetailBatch extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function transactionDetail()
    {
        return $this->belongsTo(TransactionDetail::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }
}

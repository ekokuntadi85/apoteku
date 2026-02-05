<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_batch_id',
        'type',
        'quantity',
        'remarks',
    ];

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'batch_number',
        'purchase_price',
        'stock',
        'expiration_date',
        'product_id',
        'product_unit_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}

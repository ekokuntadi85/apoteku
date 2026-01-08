<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'is_base_unit',
        'conversion_factor',
        'selling_price',
        'member_price',
        'purchase_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active_substance',
        'dosage_form',
        'sku',
        'selling_price',
        'category_id',
        'unit_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function baseUnit()
    {
        return $this->hasOne(ProductUnit::class)->where('is_base_unit', true);
    }

    public function productBatches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function getTotalStockAttribute()
    {
        return $this->productBatches->sum('stock');
    }
}

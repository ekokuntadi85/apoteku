<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use App\Models\ProductBatch;
use App\Observers\ProductBatchObserver;
use App\Models\TransactionDetail;
use App\Observers\TransactionDetailObserver;
use App\Models\StockOpnameDetail;
use App\Observers\StockOpnameDetailObserver;
use App\Models\StockOpname;
use App\Observers\StockOpnameObserver;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        ProductBatch::observe(ProductBatchObserver::class);
        TransactionDetail::observe(TransactionDetailObserver::class);
        StockOpnameDetail::observe(StockOpnameDetailObserver::class);
        StockOpname::observe(StockOpnameObserver::class);
        
        \App\Models\Transaction::observe(\App\Observers\TransactionObserver::class);
        \App\Models\Purchase::observe(\App\Observers\PurchaseObserver::class);
        \App\Models\Expense::observe(\App\Observers\ExpenseObserver::class);
    }
}
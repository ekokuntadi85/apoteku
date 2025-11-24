<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\CategoryManager;
use App\Livewire\CustomerManager;
use App\Livewire\CustomerShow;
use App\Livewire\CustomerEdit;
use App\Livewire\ProductManager;
use App\Livewire\UnitManager;
use App\Livewire\SupplierManager;
use App\Livewire\ProductCreate;
use App\Livewire\ProductEdit;
use App\Livewire\ProductShow;
use App\Livewire\PurchaseCreate;
use App\Livewire\PurchaseManager;
use App\Livewire\PurchaseShow;
use App\Livewire\PurchaseEdit;
use App\Livewire\TransactionManager;
use App\Livewire\TransactionCreate;
use App\Livewire\TransactionShow;
use App\Livewire\TransactionEdit;
use App\Livewire\PointOfSale;
use App\Livewire\PointOfSaleNew;
use App\Livewire\AccountsReceivable;
use App\Livewire\InvoiceCreate;
use App\Livewire\SalesReportCenter;
use App\Livewire\UserManager;
use App\Livewire\ExpiringStockReport;
use App\Livewire\LowStockReport;
use App\Livewire\InventoryCount;
use App\Livewire\StockCard;
use App\Livewire\DatabaseBackupManager;
use App\Livewire\DatabaseRestoreManager;
use App\Livewire\ProductImportManager;
use App\Livewire\SlowProductImportManager;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    Route::view('dashboard', 'dashboard')
        ->middleware(['verified', 'can:access-dashboard'])
        ->name('dashboard');

    Route::middleware(['can:manage-settings'])->group(function () {
        Route::redirect('settings', 'settings/profile');

        Route::get('settings/profile', Profile::class)->name('settings.profile');
        Route::get('settings/password', Password::class)->name('settings.password');
        Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    });

    // Data Management Modules
    Route::get('/categories', CategoryManager::class)->name('categories.index');
    Route::get('/units', UnitManager::class)->name('units.index');
    Route::get('/suppliers', SupplierManager::class)->name('suppliers.index');
    Route::get('/customers', CustomerManager::class)->name('customers.index');
    Route::get('/customers/{customer}', CustomerShow::class)->name('customers.show');
    Route::get('/customers/{customer}/edit', CustomerEdit::class)->name('customers.edit');
    Route::get('kartu-monitoring-suhu', \App\Livewire\KartuMonitoringSuhuManager::class)->name('kartu-monitoring-suhu');
    Route::get('kartu-monitoring-suhu/print/{month}', [App\Http\Controllers\KartuMonitoringSuhuController::class, 'printPdf'])->name('kartu-monitoring-suhu.print');

    // Product Modules
    Route::middleware(['can:access-products'])->group(function () {
        Route::get('/products', ProductManager::class)->name('products.index');
        Route::get('/products/create', ProductCreate::class)->name('products.create');
        Route::get('/products/{product}', ProductShow::class)->name('products.show');
        Route::get('/products/{product}/edit', ProductEdit::class)->name('products.edit');
    });

    // Purchase Modules
    Route::middleware(['can:access-purchases'])->group(function () {
        Route::get('/purchases', PurchaseManager::class)->name('purchases.index');
        Route::get('/purchases/create', PurchaseCreate::class)->name('purchases.create');
        Route::get('/purchases/{purchase}', PurchaseShow::class)->name('purchases.show');
        Route::get('/purchases/{purchase}/edit', PurchaseEdit::class)->name('purchases.edit');
    });
    
    // Transaction Modules
    Route::middleware(['can:access-sales'])->group(function () {
        Route::get('/transactions', TransactionManager::class)->name('transactions.index');
        Route::get('/transactions/create', TransactionCreate::class)->name('transactions.create');
        Route::get('/transactions/{transaction}', TransactionShow::class)->name('transactions.show');
        Route::get('/transactions/{transaction}/edit', TransactionEdit::class)->name('transactions.edit');
        
        // Point of Sale Module
        Route::get('/pos', PointOfSaleNew::class)->name('pos.index');
        Route::get('/pos-new', PointOfSaleNew::class)->name('pos.new');
        
        // Accounts Receivable Module
        Route::get('/accounts-receivable', AccountsReceivable::class)->name('accounts-receivable.index');
        
        Route::get('/invoices/create', InvoiceCreate::class)->name('invoices.create');
        
        // Document Printing Routes
        Route::get('/transactions/{transaction}/receipt', [App\Http\Controllers\DocumentController::class, 'printReceipt'])->name('transactions.print-receipt');
        Route::get('/transactions/{transaction}/invoice', [App\Http\Controllers\DocumentController::class, 'printInvoice'])->name('transactions.print-invoice');
    });
    
    // Reporting Modules
    Route::middleware(['can:access-reports'])->group(function () {
        Route::match(['GET', 'POST'], '/laporan-stok-terlaris', [ReportController::class, 'showReport'])->name('reports.low-stock-analysis');
        Route::get('/reports/sales', SalesReportCenter::class)->name('reports.sales');
        Route::get('/reports/expiring-stock', ExpiringStockReport::class)->name('reports.expiring-stock');
        Route::get('/reports/low-stock', LowStockReport::class)->name('reports.low-stock');
        Route::get('/reports/expiring-stock/print', [App\Http\Controllers\DocumentController::class, 'printExpiringStockReport'])->name('reports.expiring-stock.print');
        Route::get('/reports/stock-card/print', [App\Http\Controllers\DocumentController::class, 'printStockCard'])->name('reports.stock-card.print');
        Route::get('/reports/top-selling/print', [App\Http\Controllers\DocumentController::class, 'printTopSellingProductsReport'])->name('reports.top-selling.print');
    });
    
    // User Management Module
    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('/users', UserManager::class)->name('users.index');
        Route::get('database-backups', DatabaseBackupManager::class)->name('database.backup');
        Route::get('database-restore', DatabaseRestoreManager::class)->name('database.restore');
        Route::get('stock-consistency-check', \App\Livewire\StockConsistencyCheck::class)->name('stock-consistency.index');
        Route::get('artisan-commands', \App\Livewire\ArtisanCommandManager::class)->name('artisan.commands');
    });
    
    // Stock Opname Module
    Route::middleware(['can:access-products'])->group(function () {
        Route::get('/stock-card', StockCard::class)->name('stock-card.index');
        Route::get('/stock-opname', InventoryCount::class)->name('stock-opname.index');
        Route::post('/stock-opnames', [\App\Http\Controllers\StockOpnameController::class, 'store']);
        Route::post('/stock-opnames/{stockOpname}/apply', [\App\Http\Controllers\StockOpnameController::class, 'apply'])->name('stock-opnames.apply');
    });

    
    
    // Product Import Module
    Route::get('/imports', ProductImportManager::class)->name('products.import');
    Route::get('/slow-imports', SlowProductImportManager::class)->name('products.slow-import');
    

    // cetak
    Route::get('/print/receipt/{transactionId}', [App\Http\Controllers\DocumentController::class, 'printReceipt'])->name('print.receipt');
    Route::get('/print/invoice/{transactionId}', [App\Http\Controllers\DocumentController::class, 'printInvoice'])->name('print.invoice');
});

require __DIR__.'/auth.php';
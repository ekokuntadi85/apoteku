<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Invoice type: normal (penjualan biasa) or loan (pinjaman barang)
            $table->enum('invoice_type', ['normal', 'loan'])
                  ->default('normal')
                  ->after('type');
            
            // Discount and grand total
            $table->decimal('discount_amount', 10, 2)
                  ->default(0)
                  ->after('total_price');
            $table->decimal('grand_total', 10, 2)
                  ->after('discount_amount');
            
            // Payment tracking
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
        
        // Expand payment_status enum to include 'partial' and 'cancelled'
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('paid', 'unpaid', 'partial', 'cancelled') NOT NULL DEFAULT 'unpaid'");
        
        // Set grand_total for existing records
        DB::statement("UPDATE transactions SET grand_total = total_price WHERE grand_total IS NULL OR grand_total = 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert payment_status enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('paid', 'unpaid') NOT NULL DEFAULT 'unpaid'");
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['invoice_type', 'discount_amount', 'grand_total', 'paid_at']);
        });
    }
};

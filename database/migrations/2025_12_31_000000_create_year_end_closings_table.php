<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('year_end_closings', function (Blueprint $table) {
            $table->id();
            $table->integer('closing_year')->comment('Tahun yang ditutup, misal: 2024');
            $table->timestamp('closed_at')->nullable()->comment('Waktu proses selesai');
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null')->comment('User yang menjalankan');
            $table->foreignId('opening_purchase_id')->nullable()->constrained('purchases')->onDelete('set null')->comment('Purchase saldo awal yang dibuat');
            
            // Statistics
            $table->integer('total_transactions_deleted')->default(0);
            $table->integer('total_purchases_deleted')->default(0);
            $table->integer('total_journal_entries_deleted')->default(0);
            $table->integer('total_expenses_deleted')->default(0);
            $table->integer('total_payments_deleted')->default(0);
            $table->integer('total_stock_movements_deleted')->default(0);
            
            // Backup info
            $table->string('backup_file_path')->nullable()->comment('Path file backup database');
            
            // Status
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // Ensure only one closing per year
            $table->unique('closing_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('year_end_closings');
    }
};

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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('reference_number')->nullable(); // Invoice No, Receipt No
            $table->text('description')->nullable();
            
            // We use a simplified single-table structure or master-detail
            // Let's use master-detail conceptually but for simplicity in this migration
            // we will create a separate 'journal_details' table logic within the model or separate migration if strictly needed
            // But usually JournalHeader -> JournalDetail
            // Let's rename this file to create_journal_entries_table but treat it as Header
            $table->decimal('total_amount', 15, 2)->default(0); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};

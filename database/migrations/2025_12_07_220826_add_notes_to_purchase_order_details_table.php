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
        Schema::table('purchase_order_details', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            // This makes migration safe for both new and existing databases
            if (!Schema::hasColumn('purchase_order_details', 'notes')) {
                $table->text('notes')->nullable()->after('estimated_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            // Only drop if column exists
            if (Schema::hasColumn('purchase_order_details', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};

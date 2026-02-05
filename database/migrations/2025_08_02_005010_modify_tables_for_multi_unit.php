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
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
            $table->dropColumn('selling_price');
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->foreignId('product_unit_id')->nullable()->constrained('product_units')->onDelete('set null');
        });

        Schema::table('product_batches', function (Blueprint $table) {
            $table->foreignId('product_unit_id')->nullable()->constrained('product_units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('selling_price', 10, 2);
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn('product_unit_id');
        });

        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn('product_unit_id');
        });
    }
};
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
        Schema::table('product_batches', function (Blueprint $table) {
            $table->date('expiration_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            // Mengembalikan kolom ke non-nullable. Ini mungkin memerlukan nilai default
            // jika ada baris dengan nilai NULL setelah migrasi up.
            // Pertimbangkan implikasi data sebelum menjalankan down() di produksi.
            $table->date('expiration_date')->change();
        });
    }
};
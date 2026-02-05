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
        Schema::create('kartu_monitoring_suhus', function (Blueprint $table) {
            $table->id();
            $table->decimal('suhu_ruangan', 5, 2);
            $table->decimal('suhu_pendingin', 5, 2);
            $table->dateTime('waktu_pengukuran');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartu_monitoring_suhus');
    }
};

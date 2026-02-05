<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('type', ['general', 'oot', 'prekursor'])->default('general')->after('po_number');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('active_substance')->nullable()->after('name');
            $table->string('dosage_form')->nullable()->after('active_substance');
        });
    }

    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['active_substance', 'dosage_form']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->string('active_substance')->nullable()->after('product_id');
            $table->string('dosage_form')->nullable()->after('active_substance');
        });
    }

    public function down()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn(['active_substance', 'dosage_form']);
        });
    }
};

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
        Schema::table('pembayarans', function (Blueprint $table) {
            //  add expired time
            $table->string('expired_time')->nullable()->after('payment_reference');
            $table->string('payment_provider')->nullable()->after('order_id');
            $table->string('instruksi')->nullable()->after('payment_reference');
            $table->string('qr_url')->nullable()->after('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            //
            $table->dropColumn('expired_time');
            $table->dropColumn('payment_provider');
        });
    }
};
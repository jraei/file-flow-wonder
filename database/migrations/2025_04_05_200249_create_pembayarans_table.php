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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->bigInteger('price')->default(0);
            $table->integer('fee')->default(0);
            $table->integer('total_price')->default(0);
            $table->string('payment_provider')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable()->comment('3rd party reference');
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->json('instruksi')->nullable();
            $table->string('qr_url')->nullable()->comment('Qris Only');
            $table->string('payment_link')->nullable()->comment('Payment gateway link');
            $table->string('expired_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};

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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('deposit_id')->unique();
            $table->string('provider_reference')->unique();
            $table->foreignId('pay_method_id')->constrained('pay_methods');
            $table->bigInteger('amount');
            $table->integer('fee')->default(0);
            $table->string('qr_url')->nullable()->comment('Qris Only');
            $table->string('payment_link')->nullable()->comment('Payment gateway link');
            $table->string('expired_time')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};

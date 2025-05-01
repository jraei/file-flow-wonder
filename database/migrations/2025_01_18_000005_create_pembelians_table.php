
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
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('reference_id')->nullable();
            $table->string('order_type')->default('game');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('layanan_id')->constrained('layanans');
            $table->string('nickname')->nullable()->comment('Player nickname');
            $table->string('input_id')->comment('Player ID, Server ID, etc.');
            $table->string('input_zone')->nullable()->comment('Zone ID, Server name, etc.');
            $table->bigInteger('price');
            $table->bigInteger('profit');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('order_phase')->nullable()->comment('For tracking progress');
            $table->json('status_log')->nullable()->comment('Log of status changes');
            $table->json('callback_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};

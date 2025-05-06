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
        Schema::create('pay_methods', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->nullable();
            $table->string('tipe');
            $table->float('fee')->default(0);
            $table->string('fee_type')->default('fixed');
            $table->string('payment_provider')->nullable();
            $table->string('gambar')->nullable();
            $table->bigInteger('norek')->nullable();
            $table->text('keterangan')->nullable();
            $table->bigInteger('fee_fixed')->default(0);
            $table->bigInteger('min_amount')->nullable();
            $table->bigInteger('max_amount')->nullable();
            $table->float('fee_percent')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_methods');
    }
};

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
        Schema::create('layanans', function (Blueprint $table) {
            $table->id()->unique();
            $table->string('produk_id');
            $table->string('provider_id');
            $table->string('kode_layanan')->unique();
            $table->string('nama_layanan');
            $table->string('gambar')->nullable();
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->bigInteger('harga_beli_idr')->default(0);
            $table->string('catatan')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanans');
    }
};

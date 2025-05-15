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
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('developer')->nullable();
            $table->string('reference')->comment('3rd party code')->nullable();
            $table->foreignId('kategori_id')->constrained('kategoris');
            $table->integer('moogold_order_category')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->foreignId('provider_id')->constrained('providers');
            $table->string('validasi_id')->default('tidak');
            $table->text('deskripsi_game')->nullable();
            $table->text('petunjuk_field')->nullable();
            $table->string('status')->default('active');
            $table->string('thumbnail')->nullable();
            $table->string('banner')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};

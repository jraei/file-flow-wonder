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
        Schema::create('produk_input_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained()->onDelete('cascade');
            $table->string('name'); // eg. user_id, zone_id, server
            $table->string('label'); // Label yang ditampilkan di frontend
            $table->enum('type', ['text', 'number', 'select']);
            $table->boolean('required')->default(true);
            $table->integer('order')->default(0); // urutan tampil di UI
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_input_fields');
    }
};

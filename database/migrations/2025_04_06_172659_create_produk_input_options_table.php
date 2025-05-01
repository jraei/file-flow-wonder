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
        Schema::create('produk_input_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_input_field_id')->constrained()->onDelete('cascade');
            $table->string('label'); // Ditampilkan di select
            $table->string('value'); // Dikirm ke backend
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_input_options');
    }
};

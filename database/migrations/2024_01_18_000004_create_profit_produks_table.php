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
        Schema::create('profit_produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_roles_id')->constrained('user_roles');
            $table->foreignId('produk_id')->constrained('produks');
            $table->enum('type', ['percent', 'multiplier'])->default('percent');
            $table->decimal('value', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_produks');
    }
};
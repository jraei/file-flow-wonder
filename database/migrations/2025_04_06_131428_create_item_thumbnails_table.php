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
        Schema::create('item_thumbnails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->unsignedInteger('min_item')->nullable(); // nullable kalau static
            $table->unsignedInteger('max_item')->nullable(); // nullable kalau static
            $table->boolean('is_static')->default(false); // true kalau gambar tidak tergantung jumlah
            $table->boolean('default_for_produk')->default(false); // fallback default gambar
            $table->string('image_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_thumbnails');
    }
};

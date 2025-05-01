<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Insert default roles
        DB::table('user_roles')->insert([
            ['role_name' => 'admin', 'display_name' => 'Administrator', 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['role_name' => 'basic', 'display_name' => 'Basic User', 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['role_name' => 'guest', 'display_name' => 'Guest User', 'is_default' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};

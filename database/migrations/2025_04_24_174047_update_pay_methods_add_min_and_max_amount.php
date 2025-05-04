<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pay_methods', function (Blueprint $table) {
            $table->bigInteger('min_amount')->nullable()->after('fee_percent');
            $table->bigInteger('max_amount')->nullable()->after('min_amount');
        });
    }

    public function down()
    {
        Schema::table('pay_methods', function (Blueprint $table) {
            $table->dropColumn(['min_amount', 'max_amount']);
        });
    }
};

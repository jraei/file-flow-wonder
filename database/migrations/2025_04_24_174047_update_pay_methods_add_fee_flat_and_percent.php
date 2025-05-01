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
            $table->bigInteger('fee_fixed')->default(0)->after('keterangan');
            $table->float('fee_percent')->default(0)->after('fee_fixed');
            $table->dropColumn('fee'); // hapus kolom lama
        });
    }

    public function down()
    {
        Schema::table('pay_methods', function (Blueprint $table) {
            $table->dropColumn('fee_fixed');
            $table->dropColumn('fee_percent');
        });
    }
};

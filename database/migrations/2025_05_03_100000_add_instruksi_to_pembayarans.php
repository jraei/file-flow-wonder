
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
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->text('instruksi')->nullable()->after('payment_reference');
            $table->string('payment_provider')->nullable()->after('payment_reference');
            $table->string('qr_code_path')->nullable()->after('payment_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn(['instruksi', 'payment_provider', 'qr_code_path']);
        });
    }
};

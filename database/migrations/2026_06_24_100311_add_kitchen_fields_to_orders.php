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
        Schema::table('orders', function (Blueprint $table) {
            // kitchen_status: null (belum bayar) → pending → preparing → ready → delivered
            $table->string('kitchen_status')->nullable()->after('preferred_payment');
            $table->string('customer_ip', 45)->nullable()->after('customer_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['kitchen_status', 'customer_ip']);
        });
    }
};

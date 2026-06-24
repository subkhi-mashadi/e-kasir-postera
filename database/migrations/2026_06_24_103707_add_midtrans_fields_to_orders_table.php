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
            $table->string('midtrans_order_id')->nullable()->unique()->after('invoice_no');
            $table->string('midtrans_qr_url', 1000)->nullable()->after('midtrans_order_id');
            $table->string('midtrans_status')->nullable()->after('midtrans_qr_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['midtrans_order_id', 'midtrans_qr_url', 'midtrans_status']);
        });
    }
};

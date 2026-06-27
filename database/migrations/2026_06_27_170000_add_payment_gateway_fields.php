<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Companies: gateway selection + keys
        Schema::table('companies', function (Blueprint $table) {
            $table->string('payment_gateway')->default('midtrans')->after('tax_inclusive');
            $table->text('xendit_api_key')->nullable()->after('midtrans_client_key');
            $table->boolean('xendit_is_production')->default(false)->after('xendit_api_key');
            $table->text('doku_client_id')->nullable()->after('xendit_is_production');
            $table->text('doku_secret_key')->nullable()->after('doku_client_id');
            $table->string('doku_merchant_id')->nullable()->after('doku_secret_key');
            $table->string('doku_terminal_id')->default('H2H')->after('doku_merchant_id');
            $table->boolean('doku_is_production')->default(false)->after('doku_terminal_id');
        });

        // Orders: which gateway was used
        Schema::table('orders', function (Blueprint $table) {
            $table->string('gateway')->default('midtrans')->after('midtrans_status');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'xendit_api_key', 'xendit_is_production',
                'doku_client_id', 'doku_secret_key', 'doku_merchant_id', 'doku_terminal_id', 'doku_is_production',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('gateway');
        });
    }
};

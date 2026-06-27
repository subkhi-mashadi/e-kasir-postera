<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->text('xendit_secret_key')->nullable()->after('midtrans_is_production');
            $table->string('payment_provider', 20)->default('midtrans')->after('xendit_secret_key');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['xendit_secret_key', 'payment_provider']);
        });
    }
};

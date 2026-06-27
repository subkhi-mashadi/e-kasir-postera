<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['xendit_api_key', 'xendit_is_production']);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->text('xendit_api_key')->nullable()->after('midtrans_client_key');
            $table->boolean('xendit_is_production')->default(false)->after('xendit_api_key');
        });
    }
};

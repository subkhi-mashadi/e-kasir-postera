<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'doku_merchant_id')) {
                $table->string('doku_merchant_id')->nullable()->after('doku_secret_key');
                $table->string('doku_terminal_id')->default('H2H')->after('doku_merchant_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['doku_merchant_id', 'doku_terminal_id']);
        });
    }
};

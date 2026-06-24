<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('qris_image')->nullable()->after('address');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('preferred_payment')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('preferred_payment');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('qris_image');
        });
    }
};

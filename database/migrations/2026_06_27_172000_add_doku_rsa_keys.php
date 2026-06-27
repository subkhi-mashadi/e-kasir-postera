<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->text('doku_private_key')->nullable()->after('doku_terminal_id');
            $table->text('doku_public_key')->nullable()->after('doku_private_key');
            $table->text('doku_public_key_doku')->nullable()->after('doku_public_key');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['doku_private_key', 'doku_public_key', 'doku_public_key_doku']);
        });
    }
};

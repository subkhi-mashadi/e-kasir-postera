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
        Schema::table('companies', function (Blueprint $table) {
            $table->text('midtrans_server_key')->nullable()->change();
            $table->text('midtrans_client_key')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('midtrans_server_key')->nullable()->change();
            $table->string('midtrans_client_key')->nullable()->change();
        });
    }
};

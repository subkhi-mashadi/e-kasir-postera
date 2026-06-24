<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "Meja 1", "VIP 2"
            $table->string('qr_token', 64)->unique();
            $table->integer('capacity')->default(4);
            $table->string('status')->default('available'); // available, occupied, reserved
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty', 12, 3)->default(0);
            $table->decimal('min_qty', 12, 3)->default(0); // low stock alert
            $table->timestamps();

            $table->unique(['product_id', 'branch_id']);
        });

        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // sale, purchase, adjustment, transfer_in, transfer_out
            $table->decimal('qty_before', 12, 3);
            $table->decimal('qty_change', 12, 3);
            $table->decimal('qty_after', 12, 3);
            $table->string('reference_type')->nullable(); // order, purchase_order, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('tables');
    }
};

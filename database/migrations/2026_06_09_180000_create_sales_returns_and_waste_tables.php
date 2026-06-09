<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('refund_total', 10, 2);
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('completada');
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('refund_subtotal', 10, 2);
            $table->boolean('restock')->default(true);
            $table->timestamps();
        });

        Schema::create('waste_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_return_item_id')->constrained('sales_return_items')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->string('reason', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_records');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
    }
};

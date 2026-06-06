<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('code')->nullable();
            $table->string('part_number')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['producto', 'servicio'])->default('producto');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->boolean('has_tax')->default(true);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->string('barcode')->nullable();
            $table->string('compatible_brand')->nullable();
            $table->string('compatible_model')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

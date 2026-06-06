<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('social_media')->nullable();
            $table->boolean('tax_enabled')->default(false);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->enum('tax_mode', ['per_item', 'on_total'])->default('per_item');
            $table->enum('print_format', ['ticket_58mm', 'ticket_80mm', 'a4'])->default('ticket_80mm');
            $table->string('work_order_prefix')->default('OT-');
            $table->unsignedInteger('work_order_sequence')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::dropIfExists('tenants');
    }
};

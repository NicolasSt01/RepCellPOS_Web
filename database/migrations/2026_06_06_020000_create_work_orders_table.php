<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('device_brand');
            $table->string('device_model');
            $table->string('device_serial')->nullable();
            $table->string('device_imei')->nullable();
            $table->string('unlock_pattern')->nullable();
            $table->string('unlock_pin')->nullable();
            $table->text('problem_description');
            
            $table->enum('status', [
                'recibida', 'en_espera', 'en_revision', 'diagnosticada',
                'cotizacion_enviada', 'cotizacion_aprobada', 'en_reparacion',
                'reparada', 'terminada', 'cancelada'
            ])->default('recibida');
            
            $table->enum('priority', ['baja', 'media', 'alta'])->default('media');
            $table->json('timeline')->nullable();
            $table->string('work_order_number')->unique();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};

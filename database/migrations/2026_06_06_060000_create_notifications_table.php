<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->enum('channel', ['email', 'whatsapp', 'call']);
            $table->enum('event', [
                'order_created',
                'diagnosis_completed',
                'quote_sent',
                'quote_approved',
                'quote_rejected',
                'repair_completed',
                'ready_for_pickup',
            ]);
            $table->enum('status', ['pending', 'sent', 'failed', 'logged'])->default('pending');
            $table->text('message')->nullable();
            $table->text('response')->nullable();
            $table->string('tracking_token')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('tracking_token')->nullable()->unique()->after('work_order_number');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('tracking_token');
        });
        Schema::dropIfExists('notifications');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN event ENUM(
                'order_created',
                'diagnosis_completed',
                'quote_sent',
                'quote_approved',
                'quote_rejected',
                'repair_completed',
                'ready_for_pickup',
                'status_changed',
                'pickup_reminder'
            ) NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN event ENUM(
                'order_created',
                'diagnosis_completed',
                'quote_sent',
                'quote_approved',
                'quote_rejected',
                'repair_completed',
                'ready_for_pickup'
            ) NOT NULL");
        }
    }
};

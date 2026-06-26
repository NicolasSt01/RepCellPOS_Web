<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropUnique('notifications_tracking_token_unique');
            });
        } catch (\Exception $e) {
            // Index may not exist on fresh installs since the create migration
            // was updated to omit the unique constraint.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('notifications', function (Blueprint $table) {
                $table->unique('tracking_token');
            });
        } catch (\Exception $e) {
            // Ignore if unique index already exists or not supported
        }
    }
};

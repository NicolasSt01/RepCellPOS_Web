<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE quotes MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pendiente'");
        }
        // SQLite: handled by the original migration which now uses string() instead of enum()
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('pendiente','enviada','aprobada','rechazada','cobrada') NOT NULL DEFAULT 'pendiente'");
        }
    }
};

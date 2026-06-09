<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_register_movements', function (Blueprint $table) {
            $table->string('type', 50)->change();
        });

        DB::statement("UPDATE cash_register_movements SET type = 'retiro' WHERE type = 'retiro'");
    }

    public function down(): void
    {
        DB::statement("DELETE FROM cash_register_movements WHERE type NOT IN ('retiro')");
        Schema::table('cash_register_movements', function (Blueprint $table) {
            $table->string('type', 50)->change();
        });
    }
};

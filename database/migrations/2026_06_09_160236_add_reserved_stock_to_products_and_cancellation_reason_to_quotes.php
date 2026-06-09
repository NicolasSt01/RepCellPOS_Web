<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('reserved_stock')->default(0)->after('stock');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->string('cancellation_reason')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('reserved_stock');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason');
        });
    }
};

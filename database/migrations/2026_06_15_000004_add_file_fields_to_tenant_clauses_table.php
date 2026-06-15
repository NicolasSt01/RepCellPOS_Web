<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_clauses', function (Blueprint $table) {
            $table->longText('content')->nullable()->change();
            $table->string('file_path')->nullable()->after('content');
            $table->string('file_name')->nullable()->after('file_path');
            $table->string('file_url')->nullable()->after('file_name');
            $table->boolean('has_file')->default(false)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_clauses', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_name', 'file_url', 'has_file']);
            $table->longText('content')->nullable(false)->change();
        });
    }
};

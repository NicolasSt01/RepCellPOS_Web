<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('cash_amount', 10, 2)->nullable()->after('change_amount');
            $table->decimal('card_amount', 10, 2)->nullable()->after('cash_amount');
        });

        DB::statement("UPDATE sales SET cash_amount = total WHERE payment_method = 'efectivo'");
        DB::statement("UPDATE sales SET card_amount = total WHERE payment_method = 'tarjeta_transferencia'");

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE sales MODIFY payment_method VARCHAR(50) NOT NULL");
        } elseif ($driver === 'sqlite') {
            DB::statement("PRAGMA foreign_keys = OFF");

            Schema::create('sales_v2', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();
                $table->foreignId('cash_register_id')->constrained('cash_registers')->cascadeOnDelete();
                $table->string('type', 50)->default('venta_directa');
                $table->decimal('subtotal', 10, 2);
                $table->decimal('tax_total', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('total', 10, 2);
                $table->string('payment_method', 50);
                $table->string('payment_reference')->nullable();
                $table->decimal('change_amount', 10, 2)->default(0);
                $table->decimal('cash_amount', 10, 2)->nullable();
                $table->decimal('card_amount', 10, 2)->nullable();
                $table->timestamps();
            });

            $colList = ['id', 'tenant_id', 'user_id', 'client_id', 'work_order_id', 'cash_register_id', 'type', 'subtotal', 'tax_total', 'discount', 'total', 'payment_method', 'payment_reference', 'change_amount', 'cash_amount', 'card_amount', 'created_at', 'updated_at'];
            $cols = implode(', ', $colList);

            DB::statement("INSERT INTO sales_v2 ({$cols}) SELECT {$cols} FROM sales");
            DB::statement("DROP TABLE sales");
            DB::statement("ALTER TABLE sales_v2 RENAME TO sales");

            DB::statement("PRAGMA foreign_keys = ON");
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE sales SET cash_amount = NULL, card_amount = NULL");

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE sales MODIFY payment_method ENUM('efectivo', 'tarjeta_transferencia') NOT NULL");
        } elseif ($driver === 'sqlite') {
            DB::statement("PRAGMA foreign_keys = OFF");

            Schema::create('sales_v1', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();
                $table->foreignId('cash_register_id')->constrained('cash_registers')->cascadeOnDelete();
                $table->string('type', 50)->default('venta_directa');
                $table->decimal('subtotal', 10, 2);
                $table->decimal('tax_total', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('total', 10, 2);
                $table->enum('payment_method', ['efectivo', 'tarjeta_transferencia']);
                $table->string('payment_reference')->nullable();
                $table->decimal('change_amount', 10, 2)->default(0);
                $table->decimal('cash_amount', 10, 2)->nullable();
                $table->decimal('card_amount', 10, 2)->nullable();
                $table->timestamps();
            });

            $colList = ['id', 'tenant_id', 'user_id', 'client_id', 'work_order_id', 'cash_register_id', 'type', 'subtotal', 'tax_total', 'discount', 'total', 'payment_method', 'payment_reference', 'change_amount', 'cash_amount', 'card_amount', 'created_at', 'updated_at'];
            $cols = implode(', ', $colList);

            DB::statement("INSERT INTO sales_v1 ({$cols}) SELECT {$cols} FROM sales");
            DB::statement("DROP TABLE sales");
            DB::statement("ALTER TABLE sales_v1 RENAME TO sales");

            DB::statement("PRAGMA foreign_keys = ON");
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['cash_amount', 'card_amount']);
        });
    }
};

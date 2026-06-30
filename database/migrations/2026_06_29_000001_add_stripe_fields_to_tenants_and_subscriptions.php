<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->unique()->after('subscription_status');
        });

        Schema::table('tenant_subscriptions', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->unique()->after('payment_proof');
            $table->string('stripe_price_id')->nullable()->after('stripe_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('stripe_customer_id');
        });
        Schema::table('tenant_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['stripe_subscription_id', 'stripe_price_id']);
        });
    }
};

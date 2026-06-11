<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);

        $this->tenant = Tenant::factory()->create();
    }

    public function test_can_create_subscription(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'activa',
        ]);

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'status' => 'activa',
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertFalse($subscription->isExpired());
    }

    public function test_subscription_belongs_to_tenant(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'anual',
            'amount' => 2999.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'activa',
        ]);

        $this->assertInstanceOf(Tenant::class, $subscription->tenant);
        $this->assertEquals($this->tenant->id, $subscription->tenant->id);
    }

    public function test_mark_as_paid_updates_subscription(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'pendiente',
        ]);

        $subscription->markAsPaid(299.00, 'Pago-001');

        $this->assertEquals('activa', $subscription->fresh()->status);
        $this->assertNotNull($subscription->fresh()->last_payment_date);
        $this->assertNotNull($subscription->fresh()->next_payment_date);
        $this->assertCount(1, $subscription->fresh()->payment_history);
    }

    public function test_can_access_subscription_from_tenant(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
        ]);

        $this->assertNotNull($this->tenant->subscription);
        $this->assertEquals($subscription->id, $this->tenant->subscription->id);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSubscriptionTest extends TestCase
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

    public function test_show_create_subscription_form(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.subscriptions.create', $this->tenant));

        $response->assertOk();
        $response->assertSee('Crear Suscripción');
    }

    public function test_can_create_subscription_via_ui(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('admin.subscriptions.store', $this->tenant), [
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
        ]);

        $response->assertRedirect(route('admin.tenants.show', $this->tenant));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'status' => 'activa',
        ]);
    }

    public function test_can_update_subscription(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
        ]);

        $response = $this->actingAs($this->superadmin)->put(route('admin.subscriptions.update', [$this->tenant, $subscription]), [
            'plan_type' => 'anual',
            'amount' => 2999.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
        ]);

        $response->assertRedirect(route('admin.tenants.show', $this->tenant));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenant_subscriptions', [
            'id' => $subscription->id,
            'plan_type' => 'anual',
            'amount' => 2999.00,
        ]);
    }

    public function test_can_mark_subscription_as_paid(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->subMonth()->toDateString(),
            'status' => 'pendiente',
        ]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.subscriptions.pay', [$this->tenant, $subscription]), [
            'amount' => 299.00,
            'reference' => 'PAGO-001',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $subscription->refresh();
        $this->assertEquals('activa', $subscription->status);
        $this->assertNotNull($subscription->last_payment_date);
        $this->assertCount(1, $subscription->payment_history);
    }

    public function test_validation_requires_plan_type(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('admin.subscriptions.store', $this->tenant), [
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
        ]);

        $response->assertSessionHasErrors('plan_type');
    }
}

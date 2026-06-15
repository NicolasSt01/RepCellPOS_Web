<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminFinanceTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);
    }

    public function test_superadmin_can_access_finances_page(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.finances'));

        $response->assertOk();
        $response->assertSee('Finanzas');
    }

    public function test_finances_page_shows_key_metrics(): void
    {
        $tenant = Tenant::factory()->create();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
            'paid_via' => 'transferencia',
        ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'mensual',
            'amount' => 199.00,
            'start_date' => now()->toDateString(),
            'status' => 'pendiente',
            'payment_proof' => 'proofs/test.pdf',
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.finances'));

        $response->assertOk();
        $response->assertSee('498.00');
        $response->assertSee('299.00');
        $response->assertSee('199.00');
    }

    public function test_confirm_payment_updates_subscription_and_tenant(): void
    {
        $plan = Plan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 299.00,
            'is_active' => true,
            'features' => ['feature1' => true],
            'limits' => ['users' => 5],
        ]);

        $tenant = Tenant::factory()->create([
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(5),
        ]);

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'pendiente',
            'payment_proof' => 'proofs/test.pdf',
        ]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.finances.confirm', $subscription->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $subscription->refresh();
        $tenant->refresh();

        $this->assertEquals('activa', $subscription->status);
        $this->assertEquals($plan->id, $tenant->plan_id);
        $this->assertEquals('active', $tenant->subscription_status);
        $this->assertNull($tenant->trial_ends_at);
    }

    public function test_reject_payment_sets_status_to_rechazado(): void
    {
        $tenant = Tenant::factory()->create();

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'pendiente',
            'payment_proof' => 'proofs/test.pdf',
        ]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.finances.reject', $subscription->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $subscription->refresh();
        $this->assertEquals('rechazado', $subscription->status);
    }

    public function test_export_finances_returns_csv(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 299.00,
            'is_active' => true,
            'features' => ['feature1' => true],
            'limits' => ['users' => 5],
        ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'activa',
            'paid_via' => 'transferencia',
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.finances.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="finanzas.csv"');

        $content = $response->streamedContent();
        $this->assertStringContainsString($tenant->name, $content);
        $this->assertStringContainsString('Premium', $content);
        $this->assertStringContainsString('299.00', $content);
    }
}

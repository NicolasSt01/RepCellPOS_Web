<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private Plan $plan;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Plan profesional.',
            'price' => 199,
            'features' => ['work_orders' => true, 'quotes' => true, 'pos' => true, 'notifications_email' => true, 'notifications_whatsapp' => false, 'reports_advanced' => false],
            'limits' => ['max_users' => 5, 'max_clients' => -1, 'max_monthly_work_orders' => 200, 'storage_mb' => 1000],
            'sort_order' => 2,
            'is_active' => true,
            'is_highlight' => true,
        ]);
    }

    private function createTenantUser(string $subscriptionStatus, ?string $trialEndsAt = null): User
    {
        $tenant = Tenant::factory()->create([
            'plan_id' => $this->plan->id,
            'subscription_status' => $subscriptionStatus,
            'trial_ends_at' => $trialEndsAt,
            'is_active' => true,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        Role::firstOrCreate(['name' => 'admin_tenant', 'guard_name' => 'web']);
        $user->assignRole('admin_tenant');

        return $user;
    }

    public function test_trial_tenant_can_access_dashboard(): void
    {
        $user = $this->createTenantUser('trial', now()->addDays(5));

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_expired_trial_tenant_is_redirected_to_upgrade(): void
    {
        $user = $this->createTenantUser('trial', now()->subDay());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('subscription.upgrade'));
        $response->assertSessionHas('error');
    }

    public function test_expired_trial_updates_subscription_status(): void
    {
        $tenant = Tenant::factory()->create([
            'plan_id' => $this->plan->id,
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user)->get(route('dashboard'));

        $this->assertEquals('expired', $tenant->fresh()->subscription_status);
    }

    public function test_active_subscription_tenant_can_access_dashboard(): void
    {
        $user = $this->createTenantUser('active');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_cancelled_tenant_is_redirected_to_upgrade(): void
    {
        $user = $this->createTenantUser('cancelled');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('subscription.upgrade'));
        $response->assertSessionHas('error');
    }

    public function test_expired_tenant_is_redirected_to_upgrade(): void
    {
        $user = $this->createTenantUser('expired');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('subscription.upgrade'));
        $response->assertSessionHas('error');
    }

    public function test_superadmin_never_blocked(): void
    {
        $superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);

        $response = $this->actingAs($superadmin)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_inactive_tenant_is_logged_out_and_redirected(): void
    {
        $tenant = Tenant::factory()->create([
            'plan_id' => $this->plan->id,
            'subscription_status' => 'active',
            'is_active' => false,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}

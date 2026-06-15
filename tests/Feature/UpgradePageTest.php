<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpgradePageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;
    private Plan $basic;
    private Plan $pro;
    private Plan $premium;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');

        $this->basic = Plan::create([
            'name' => 'Básico',
            'slug' => 'basic',
            'description' => 'Plan básico.',
            'price' => 99,
            'features' => ['work_orders' => true, 'quotes' => false, 'pos' => false, 'notifications_email' => false, 'notifications_whatsapp' => false, 'reports_advanced' => false],
            'limits' => ['max_users' => 2, 'max_clients' => 50, 'max_monthly_work_orders' => 50, 'storage_mb' => 200],
            'sort_order' => 1,
            'is_active' => true,
            'is_highlight' => false,
        ]);

        $this->pro = Plan::create([
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

        $this->premium = Plan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Plan premium.',
            'price' => 349,
            'features' => ['work_orders' => true, 'quotes' => true, 'pos' => true, 'notifications_email' => true, 'notifications_whatsapp' => true, 'reports_advanced' => true],
            'limits' => ['max_users' => 10, 'max_clients' => -1, 'max_monthly_work_orders' => -1, 'storage_mb' => 2000],
            'sort_order' => 3,
            'is_active' => true,
            'is_highlight' => false,
        ]);

        $this->tenant = Tenant::factory()->create([
            'plan_id' => $this->basic->id,
            'subscription_status' => 'expired',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_upgrade_page_loads_with_plans(): void
    {
        $response = $this->actingAs($this->user)->get(route('subscription.upgrade'));

        $response->assertOk();
        $response->assertSee('Básico');
        $response->assertSee('Pro');
        $response->assertSee('Premium');
        $response->assertSee('$99.00');
        $response->assertSee('$199.00');
        $response->assertSee('$349.00');
    }

    public function test_upgrade_page_shows_current_plan(): void
    {
        $response = $this->actingAs($this->user)->get(route('subscription.upgrade'));

        $response->assertOk();
        $response->assertSee($this->basic->name);
    }

    public function test_select_plan_creates_pending_subscription(): void
    {
        $response = $this->actingAs($this->user)->post(route('subscription.select'), [
            'plan_id' => $this->pro->id,
        ]);

        $response->assertRedirect(route('subscription.upgrade'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->pro->id,
            'plan_type' => 'pro',
            'amount' => 199,
            'status' => 'pendiente',
        ]);
    }

    public function test_upload_proof_stores_file(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->pro->id,
            'plan_type' => 'pro',
            'amount' => 199,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'pendiente',
        ]);

        $file = UploadedFile::fake()->image('comprobante.jpg');

        $response = $this->actingAs($this->user)->post(route('subscription.payment-proof'), [
            'subscription_id' => $subscription->id,
            'payment_proof' => $file,
        ]);

        $response->assertRedirect(route('subscription.upgrade'));
        $response->assertSessionHas('success');

        $subscription->refresh();
        $this->assertNotNull($subscription->payment_proof);
        $this->assertEquals('transfer', $subscription->paid_via);

        Storage::disk('r2')->assertExists($subscription->payment_proof);
    }

    public function test_select_plan_validates_required_plan_id(): void
    {
        $response = $this->actingAs($this->user)->post(route('subscription.select'), [
            'plan_id' => '',
        ]);

        $response->assertSessionHasErrors('plan_id');
    }

    public function test_select_plan_validates_plan_exists(): void
    {
        $response = $this->actingAs($this->user)->post(route('subscription.select'), [
            'plan_id' => 999,
        ]);

        $response->assertSessionHasErrors('plan_id');
    }

    public function test_upgrade_page_shows_pending_payment_info(): void
    {
        TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->pro->id,
            'plan_type' => 'pro',
            'amount' => 199,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'pendiente',
        ]);

        $response = $this->actingAs($this->user)->get(route('subscription.upgrade'));

        $response->assertOk();
        $response->assertSee('Pago pendiente');
        $response->assertSee('199.00');
    }
}

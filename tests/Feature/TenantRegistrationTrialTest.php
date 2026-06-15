<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantRegistrationTrialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        Plan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Máximo poder para talleres establecidos.',
            'price' => 349,
            'features' => [
                'work_orders' => true,
                'quotes' => true,
                'pos' => true,
                'notifications_email' => true,
                'notifications_whatsapp' => true,
                'reports_advanced' => true,
            ],
            'limits' => [
                'max_users' => 10,
                'max_clients' => -1,
                'max_monthly_work_orders' => -1,
                'storage_mb' => 2000,
            ],
            'sort_order' => 3,
            'is_active' => true,
            'is_highlight' => false,
        ]);
    }

    public function test_registration_creates_tenant_with_trial_fields(): void
    {
        $this->post('/register', [
            'business_name' => 'Mi Taller',
            'business_phone' => '555-1234',
            'admin_name' => 'Admin User',
            'admin_email' => 'admin@example.com',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'password',
        ]);

        $tenant = Tenant::where('slug', 'like', 'mi-taller-%')->first();

        $this->assertNotNull($tenant);
        $this->assertEquals('trial', $tenant->subscription_status);
        $this->assertNotNull($tenant->trial_ends_at);
        $this->assertEquals(now()->addDays(7)->toDateString(), $tenant->trial_ends_at->toDateString());
        $this->assertEquals(Plan::where('slug', 'premium')->first()->id, $tenant->plan_id);
    }

    public function test_registration_creates_user_and_logs_in(): void
    {
        $this->post('/register', [
            'business_name' => 'Mi Taller 2',
            'business_phone' => '555-5678',
            'admin_name' => 'Admin User',
            'admin_email' => 'admin2@example.com',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin2@example.com',
            'name' => 'Admin User',
        ]);

        $user = User::where('email', 'admin2@example.com')->first();
        $this->assertTrue($user->hasRole('admin_tenant'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_redirects_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'business_name' => 'Mi Taller 3',
            'business_phone' => '555-9012',
            'admin_name' => 'Admin User',
            'admin_email' => 'admin3@example.com',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
    }
}

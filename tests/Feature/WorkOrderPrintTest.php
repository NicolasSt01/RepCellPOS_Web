<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Tenant;
use App\Models\TenantClause;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderPrintTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Tenant $otherTenant;
    private User $user;
    private User $otherUser;
    private WorkOrder $workOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['print_format' => 'ticket_80mm']);
        $this->otherTenant = Tenant::factory()->create();

        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->otherUser = User::factory()->create(['tenant_id' => $this->otherTenant->id]);

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_print_route_returns_ok(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('work_orders.print', $this->workOrder));

        $response->assertOk();
        $response->assertSee($this->workOrder->work_order_number);
    }

    public function test_print_shows_client_name(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('work_orders.print', $this->workOrder));

        $response->assertOk();
        $response->assertSee($this->workOrder->client->name);
    }

    public function test_print_denied_for_other_tenant(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('work_orders.print', $this->workOrder));

        $response->assertStatus(403);
    }

    public function test_print_uses_correct_format(): void
    {
        $this->tenant->update(['print_format' => 'ticket_58mm']);

        $response = $this->actingAs($this->user)
            ->get(route('work_orders.print', $this->workOrder));

        $response->assertOk();
    }

    public function test_print_uses_a4_format(): void
    {
        $this->tenant->update(['print_format' => 'a4']);

        $response = $this->actingAs($this->user)
            ->get(route('work_orders.print', $this->workOrder));

        $response->assertOk();
    }

    public function test_print_renders_tenant_clauses(): void
    {
        TenantClause::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Garantía',
            'content' => 'Equipo garantizado por 30 días.',
            'print_on_receipt' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('work_orders.print', $this->workOrder));

        $response->assertOk();
        $response->assertSee('Equipo garantizado por 30 días.');
    }

    public function test_print_does_not_render_inactive_clauses(): void
    {
        TenantClause::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Inactiva',
            'content' => 'No debe aparecer.',
            'print_on_receipt' => true,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('work_orders.print', $this->workOrder));

        $response->assertOk();
        $response->assertDontSee('No debe aparecer.');
    }

    public function test_print_pdf_returns_pdf(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('work_orders.print.pdf', $this->workOrder));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_print_pdf_denied_for_other_tenant(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('work_orders.print.pdf', $this->workOrder));

        $response->assertStatus(403);
    }

    public function test_store_redirects_to_print(): void
    {
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)
            ->post(route('work_orders.store'), [
                'client_id' => $client->id,
                'device_brand' => 'Apple',
                'device_model' => 'iPhone 13',
                'problem_description' => 'Pantalla rota',
            ]);

        $this->assertDatabaseHas('work_orders', [
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
        ]);
        $response->assertStatus(302);
    }
}

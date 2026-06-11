<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\Client;
use Spatie\Permission\Models\Permission;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\TenantClause;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Tenant $otherTenant;
    private User $user;
    private User $otherUser;
    private Sale $sale;
    private Sale $otherSale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['print_format' => 'ticket_58mm']);
        $this->otherTenant = Tenant::factory()->create();

        Permission::create(['guard_name' => 'web', 'name' => 'pos.access']);

        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->user->givePermissionTo('pos.access');
        $this->otherUser = User::factory()->create(['tenant_id' => $this->otherTenant->id]);

        $cashRegister = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $this->sale = Sale::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'cash_register_id' => $cashRegister->id,
            'type' => 'venta_directa',
            'subtotal' => 100,
            'tax_total' => 16,
            'total' => 116,
            'payment_method' => 'efectivo',
            'cash_amount' => 116,
            'change_amount' => 0,
        ]);

        // Sale from another tenant for auth tests
        $otherCashRegister = CashRegister::create([
            'tenant_id' => $this->otherTenant->id,
            'user_id' => $this->otherUser->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $this->otherSale = Sale::create([
            'tenant_id' => $this->otherTenant->id,
            'user_id' => $this->otherUser->id,
            'cash_register_id' => $otherCashRegister->id,
            'type' => 'venta_directa',
            'subtotal' => 200,
            'tax_total' => 32,
            'total' => 232,
            'payment_method' => 'efectivo',
            'cash_amount' => 232,
            'change_amount' => 0,
        ]);
    }

    public function test_print_route_returns_ok(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
        $response->assertSee('Ticket #' . $this->sale->id);
    }

    public function test_print_preview_returns_ok(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pos.print.preview', $this->sale));

        $response->assertOk();
    }

    public function test_print_denied_for_other_tenant(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('pos.print', $this->sale));

        $response->assertStatus(403);
    }

    public function test_print_preview_denied_for_other_tenant(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('pos.print.preview', $this->sale));

        $response->assertStatus(403);
    }

    public function test_print_renders_correct_format(): void
    {
        $this->tenant->update(['print_format' => 'ticket_80mm']);

        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
        $response->assertSee('80mm');
    }

    public function test_print_renders_a4_format(): void
    {
        $this->tenant->update(['print_format' => 'a4']);

        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
    }

    public function test_preview_does_not_auto_print(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pos.print.preview', $this->sale));

        $response->assertOk();
        $this->assertStringNotContainsString('onload="window.print()"', $response->getContent());
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
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
        $response->assertSee('Equipo garantizado por 30 días.');
    }

    public function test_print_does_not_render_inactive_clauses(): void
    {
        TenantClause::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Garantía',
            'content' => 'No debe aparecer.',
            'print_on_receipt' => true,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
        $response->assertDontSee('No debe aparecer.');
    }

    public function test_print_shows_client_name_when_available(): void
    {
        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Juan Pérez',
        ]);
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
        ]);
        $this->sale->update(['work_order_id' => $workOrder->id]);

        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
        $response->assertSee('Juan Pérez');
    }

    public function test_sales_print_redirects_to_pos_print(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('sales.print', $this->sale));

        $response->assertRedirect(route('pos.print', $this->sale));
    }

    public function test_sales_print_denied_for_other_tenant(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('sales.print', $this->sale));

        $response->assertStatus(403);
    }
}

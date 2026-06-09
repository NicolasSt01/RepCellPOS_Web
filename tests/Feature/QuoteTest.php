<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private WorkOrder $workOrder;
    private CashRegister $cashRegister;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create([
            'tax_enabled' => true,
            'tax_percentage' => 16,
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
            'status' => 'diagnosticada',
        ]);

        $this->cashRegister = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);
    }

    public function test_quote_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $response->assertOk();
        $response->assertSee('Cotización');
        $response->assertSee($this->workOrder->work_order_number);
    }

    public function test_quote_is_auto_created_when_viewing(): void
    {
        $this->assertNull($this->workOrder->quote);

        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $this->workOrder->refresh();
        $this->assertNotNull($this->workOrder->quote);
        $this->assertEquals('pendiente', $this->workOrder->quote->status);
    }

    public function test_can_add_service_item_to_quote(): void
    {
        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $response = $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'type' => 'servicio',
                'description' => 'Diagnóstico completo',
                'quantity' => 1,
                'unit_price' => 350,
                'tax_percentage' => 16,
            ]);

        $response->assertRedirect(route('quotes.show', $this->workOrder));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('quote_items', [
            'quote_id' => $quote->id,
            'description' => 'Diagnóstico completo',
            'quantity' => 1,
            'unit_price' => 350,
            'type' => 'servicio',
        ]);
    }

    public function test_can_add_product_item_with_reference(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pantalla iPhone 13',
            'type' => 'producto',
            'purchase_price' => 1500,
            'sale_price' => 2500,
            'tax_percentage' => 16,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $response = $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'product_id' => $product->id,
                'type' => 'producto',
                'description' => $product->name,
                'quantity' => 2,
                'unit_price' => $product->sale_price,
                'tax_percentage' => $product->tax_percentage,
            ]);

        $response->assertRedirect(route('quotes.show', $this->workOrder));

        $this->assertDatabaseHas('quote_items', [
            'quote_id' => $quote->id,
            'product_id' => $product->id,
            'description' => 'Pantalla iPhone 13',
            'quantity' => 2,
            'unit_price' => 2500,
        ]);
    }

    public function test_adding_items_recalculates_quote_totals(): void
    {
        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'type' => 'servicio',
                'description' => 'Mano de obra',
                'quantity' => 1,
                'unit_price' => 500,
                'tax_percentage' => 16,
            ]);

        $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'type' => 'servicio',
                'description' => 'Respaldo de datos',
                'quantity' => 1,
                'unit_price' => 200,
                'tax_percentage' => 16,
            ]);

        $quote->refresh();

        $this->assertEquals(700, $quote->subtotal);
        $this->assertEquals(112, $quote->tax_total); // 700 * 0.16
        $this->assertEquals(812, $quote->total);
    }

    public function test_can_remove_item_from_quote(): void
    {
        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'type' => 'servicio',
                'description' => 'Item a eliminar',
                'quantity' => 1,
                'unit_price' => 100,
                'tax_percentage' => 0,
            ]);

        $item = $quote->quoteItems()->first();

        $response = $this->actingAs($this->user)
            ->delete(route('quotes.remove_item', $item));

        $response->assertRedirect(route('quotes.show', $this->workOrder));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('quote_items', ['id' => $item->id]);
    }

    public function test_can_send_quote(): void
    {
        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'type' => 'servicio',
                'description' => 'Reparación',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_percentage' => 16,
            ]);

        $response = $this->actingAs($this->user)
            ->post(route('quotes.send', $quote));

        $response->assertRedirect(route('work_orders.show', $this->workOrder));
        $response->assertSessionHas('success');

        $quote->refresh();
        $this->assertEquals('enviada', $quote->status);

        $this->workOrder->refresh();
        $this->assertEquals('cotizacion_enviada', $this->workOrder->status);
    }

    public function test_can_approve_quote(): void
    {
        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'type' => 'servicio',
                'description' => 'Reparación',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_percentage' => 16,
            ]);

        $this->actingAs($this->user)
            ->post(route('quotes.send', $quote));

        $quote->refresh();
        $this->assertEquals('enviada', $quote->status);

        $response = $this->actingAs($this->user)
            ->post(route('quotes.approve', $quote));

        $response->assertRedirect(route('work_orders.show', $this->workOrder));
        $response->assertSessionHas('success');

        $quote->refresh();
        $this->assertEquals('aprobada', $quote->status);

        $this->workOrder->refresh();
        $this->assertEquals('cotizacion_aprobada', $this->workOrder->status);
    }

    public function test_can_reject_quote(): void
    {
        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'type' => 'servicio',
                'description' => 'Reparación',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_percentage' => 16,
            ]);

        $this->actingAs($this->user)
            ->post(route('quotes.send', $quote));

        $quote->refresh();

        $response = $this->actingAs($this->user)
            ->post(route('quotes.reject', $quote), [
                'reason' => 'Cliente no aceptó el precio',
            ]);

        $response->assertRedirect(route('work_orders.show', $this->workOrder));
        $response->assertSessionHas('success');

        $quote->refresh();
        $this->assertEquals('rechazada', $quote->status);

        $this->workOrder->refresh();
        $this->assertEquals('cancelada', $this->workOrder->status);
    }

    public function test_cannot_approve_already_approved_quote(): void
    {
        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'type' => 'servicio',
                'description' => 'Reparación',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_percentage' => 16,
            ]);

        $this->actingAs($this->user)->post(route('quotes.send', $quote));
        $quote->refresh();

        $this->actingAs($this->user)->post(route('quotes.approve', $quote));
        $quote->refresh();
        $this->assertEquals('aprobada', $quote->status);

        $response = $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $response->assertOk();
    }

    // --- Cobro de Orden desde POS ---

    public function test_pos_shows_work_order_banner_for_approved_quote(): void
    {
        $this->createApprovedQuote();

        $response = $this->actingAs($this->user)
            ->get(route('pos.index', ['work_order_id' => $this->workOrder->id]));

        $response->assertOk();
        $response->assertSee('Cobro de orden');
        $response->assertSee($this->workOrder->work_order_number);
    }

    public function test_pos_does_not_show_banner_for_non_approved_quote(): void
    {
        // Quote is pendiente (not approved)
        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $response = $this->actingAs($this->user)
            ->get(route('pos.index', ['work_order_id' => $this->workOrder->id]));

        $response->assertOk();
        $response->assertDontSee('Cobro de orden');
    }

    public function test_can_charge_approved_quote_from_pos(): void
    {
        $this->createApprovedQuote();

        $response = $this->actingAs($this->user)
            ->post(route('pos.checkout'), [
                'work_order_id' => $this->workOrder->id,
                'items' => [
                    [
                        'type' => 'servicio',
                        'description' => 'Mano de obra',
                        'quantity' => 1,
                        'unit_price' => 500,
                        'tax_percentage' => 16,
                    ],
                    [
                        'type' => 'servicio',
                        'description' => 'Diagnóstico',
                        'quantity' => 1,
                        'unit_price' => 300,
                        'tax_percentage' => 16,
                    ],
                ],
                'payment_method' => 'efectivo',
                'amount_received' => 1000,
            ]);

        $response->assertRedirect(route('pos.index'));

        $this->assertDatabaseHas('sales', [
            'work_order_id' => $this->workOrder->id,
            'type' => 'cobro_orden',
            'subtotal' => 800,
            'tax_total' => 128,
            'total' => 928,
        ]);

        $this->workOrder->refresh();
        $this->assertEquals('en_reparacion', $this->workOrder->status);
    }

    public function test_cannot_charge_non_approved_quote(): void
    {
        // Quote exists but is pendiente
        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $response = $this->actingAs($this->user)
            ->post(route('pos.checkout'), [
                'work_order_id' => $this->workOrder->id,
                'items' => [
                    [
                        'type' => 'servicio',
                        'description' => 'Reparación',
                        'quantity' => 1,
                        'unit_price' => 500,
                        'tax_percentage' => 16,
                    ],
                ],
                'payment_method' => 'efectivo',
                'amount_received' => 600,
            ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('error');
    }

    private function createApprovedQuote(): void
    {
        $this->actingAs($this->user)
            ->get(route('quotes.show', $this->workOrder));

        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'type' => 'servicio',
                'description' => 'Mano de obra',
                'quantity' => 1,
                'unit_price' => 500,
                'tax_percentage' => 16,
            ]);

        $this->actingAs($this->user)
            ->post(route('quotes.add_item', $quote), [
                'type' => 'servicio',
                'description' => 'Diagnóstico',
                'quantity' => 1,
                'unit_price' => 300,
                'tax_percentage' => 16,
            ]);

        $this->actingAs($this->user)->post(route('quotes.send', $quote));
        $quote->refresh();

        $this->actingAs($this->user)->post(route('quotes.approve', $quote));
        $this->workOrder->refresh();
    }
}

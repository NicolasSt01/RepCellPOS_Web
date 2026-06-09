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

    public function test_adding_product_does_not_reserve_until_approved(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 4,
            'reserved_stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)->post(route('quotes.add_item', $quote), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);

        $product->refresh();
        $this->assertEquals(4, $product->stock);
        $this->assertEquals(0, $product->reserved_stock);
        $this->assertEquals(4, $product->availableStock());

        $quote->refresh();
        $this->assertEquals(1, $quote->quoteItems()->count());
    }

    public function test_approving_quote_reserves_stock(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 4,
            'reserved_stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)->post(route('quotes.add_item', $quote), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);

        $this->actingAs($this->user)->post(route('quotes.send', $quote));
        $this->actingAs($this->user)->post(route('quotes.approve', $quote));

        $product->refresh();
        $this->assertEquals(4, $product->stock);
        $this->assertEquals(2, $product->reserved_stock);
        $this->assertEquals(2, $product->availableStock());

        $quote->refresh();
        $this->assertEquals('aprobada', $quote->status);
    }

    public function test_removing_item_from_pending_quote_does_not_affect_stock(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 4,
            'reserved_stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)->post(route('quotes.add_item', $quote), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);

        $item = $quote->quoteItems()->where('product_id', $product->id)->first();

        $this->actingAs($this->user)->delete(route('quotes.remove_item', $item));

        $product->refresh();
        $this->assertEquals(4, $product->stock);
        $this->assertEquals(0, $product->reserved_stock);
    }

    public function test_removing_item_from_approved_quote_releases_reservation(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 4,
            'reserved_stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)->post(route('quotes.add_item', $quote), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);

        $this->actingAs($this->user)->post(route('quotes.send', $quote));
        $this->actingAs($this->user)->post(route('quotes.approve', $quote));

        $product->refresh();
        $this->assertEquals(2, $product->reserved_stock);

        $item = $quote->quoteItems()->where('product_id', $product->id)->first();
        $this->actingAs($this->user)->delete(route('quotes.remove_item', $item));

        $product->refresh();
        $this->assertEquals(0, $product->reserved_stock);
        $this->assertEquals(4, $product->availableStock());
    }

    public function test_adding_product_with_insufficient_stock_fails(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 4,
            'reserved_stock' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $response = $this->actingAs($this->user)->post(route('quotes.add_item', $quote), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 3,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('quote_items', [
            'quote_id' => $quote->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_approving_quote_reserves_stock_not_consumes(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pantalla',
            'type' => 'producto',
            'purchase_price' => 500,
            'sale_price' => 800,
            'stock' => 5,
            'reserved_stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)->post(route('quotes.add_item', $quote), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);

        $this->actingAs($this->user)->post(route('quotes.send', $quote));
        $this->actingAs($this->user)->post(route('quotes.approve', $quote));

        // Approval only reserves, does NOT consume stock yet
        $product->refresh();
        $this->assertEquals(5, $product->stock);
        $this->assertEquals(2, $product->reserved_stock);
        $this->assertEquals(3, $product->availableStock());

        // No kardex movement yet — that happens at POS payment
        $this->assertDatabaseMissing('kardex_movements', [
            'product_id' => $product->id,
            'type' => 'salida',
            'reference_type' => 'App\Models\Quote',
            'reference_id' => $quote->id,
        ]);

        $quote->refresh();
        $this->assertEquals('aprobada', $quote->status);

        $this->workOrder->refresh();
        $this->assertEquals('cotizacion_aprobada', $this->workOrder->status);
    }

    public function test_pos_payment_of_approved_quote_consumes_stock_and_records_kardex(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pantalla',
            'type' => 'producto',
            'purchase_price' => 500,
            'sale_price' => 800,
            'stock' => 5,
            'reserved_stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)->post(route('quotes.add_item', $quote), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);

        $this->actingAs($this->user)->post(route('quotes.send', $quote));
        $this->actingAs($this->user)->post(route('quotes.approve', $quote));

        $product->refresh();
        $this->assertEquals(2, $product->reserved_stock);

        // Pay via POS (cobro_orden)
        $response = $this->actingAs($this->user)->post(route('pos.checkout'), [
            'work_order_id' => $this->workOrder->id,
            'items' => [[
                'product_id' => $product->id,
                'type' => 'producto',
                'description' => $product->name,
                'quantity' => 2,
                'unit_price' => $product->sale_price,
                'tax_percentage' => 0,
            ]],
            'payment_method' => 'efectivo',
            'amount_received' => 2000,
        ]);

        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals(3, $product->stock);
        $this->assertEquals(0, $product->reserved_stock);

        $this->assertDatabaseHas('kardex_movements', [
            'product_id' => $product->id,
            'type' => 'salida',
            'quantity' => 2,
            'reference_type' => 'App\Models\Quote',
            'reference_id' => $quote->id,
        ]);

        $this->workOrder->refresh();
        $this->assertEquals('en_reparacion', $this->workOrder->status);
    }

    public function test_approving_quote_with_insufficient_stock_fails(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pantalla',
            'type' => 'producto',
            'purchase_price' => 500,
            'sale_price' => 800,
            'stock' => 2,
            'reserved_stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)->post(route('quotes.add_item', $quote), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);

        // Another quote is APPROVED first, reserving the same product
        $wo2 = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->workOrder->client_id,
            'user_id' => $this->user->id,
            'status' => 'diagnosticada',
        ]);
        $this->actingAs($this->user)->get(route('quotes.show', $wo2));
        $wo2->refresh();
        $q2 = $wo2->quote;
        $this->actingAs($this->user)->post(route('quotes.add_item', $q2), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);
        $this->actingAs($this->user)->post(route('quotes.send', $q2));
        $this->actingAs($this->user)->post(route('quotes.approve', $q2));
        // Now reserved_stock=2, stock=2, available_stock=0

        $this->actingAs($this->user)->post(route('quotes.send', $quote));
        $response = $this->actingAs($this->user)->post(route('quotes.approve', $quote));
        $response->assertSessionHas('error');
    }

    public function test_rejecting_approved_quote_releases_reserved_stock(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 4,
            'reserved_stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)->post(route('quotes.add_item', $quote), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);

        $this->actingAs($this->user)->post(route('quotes.send', $quote));
        $this->actingAs($this->user)->post(route('quotes.approve', $quote));

        $product->refresh();
        $this->assertEquals(2, $product->reserved_stock);

        $this->actingAs($this->user)->post(route('quotes.reject', $quote), [
            'reason' => 'Cliente no aceptó precio',
        ]);

        $product->refresh();
        $this->assertEquals(0, $product->reserved_stock);
        $this->assertEquals(4, $product->availableStock());

        $quote->refresh();
        $this->assertEquals('rechazada', $quote->status);
        $this->assertEquals('Cliente no aceptó precio', $quote->cancellation_reason);

        $this->workOrder->refresh();
        $this->assertEquals('cancelada', $this->workOrder->status);
    }

    public function test_pos_checkout_respects_available_stock(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 3,
            'reserved_stock' => 2, // reserved by an approved quote
            'is_active' => true,
        ]);

        // available_stock = 3 - 2 = 1, trying to buy 4 should fail
        $response = $this->actingAs($this->user)->post(route('pos.checkout'), [
            'items' => [[
                'product_id' => $product->id,
                'type' => 'producto',
                'description' => $product->name,
                'quantity' => 4,
                'unit_price' => $product->sale_price,
                'tax_percentage' => 0,
            ]],
            'payment_method' => 'efectivo',
            'amount_received' => 1000,
        ]);

        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');
    }

    public function test_pos_sale_auto_cancels_pending_quotes_when_stock_insufficient(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 4,
            'reserved_stock' => 0,
            'is_active' => true,
        ]);

        // Create a pending quote (does NOT reserve stock)
        $this->actingAs($this->user)->get(route('quotes.show', $this->workOrder));
        $this->workOrder->refresh();
        $quote = $this->workOrder->quote;

        $this->actingAs($this->user)->post(route('quotes.add_item', $quote), [
            'product_id' => $product->id,
            'type' => 'producto',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->sale_price,
            'tax_percentage' => 0,
        ]);

        $product->refresh();
        $this->assertEquals(0, $product->reserved_stock); // Pending = no reservation

        // Sell 3 units via POS — stock becomes 1, quote needs 2 → auto-cancel
        $response = $this->actingAs($this->user)->post(route('pos.checkout'), [
            'items' => [[
                'product_id' => $product->id,
                'type' => 'producto',
                'description' => $product->name,
                'quantity' => 3,
                'unit_price' => $product->sale_price,
                'tax_percentage' => 0,
            ]],
            'payment_method' => 'efectivo',
            'amount_received' => 1000,
        ]);

        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals(1, $product->stock);

        // The pending quote should have been auto-cancelled
        $quote->refresh();
        $this->assertEquals('rechazada', $quote->status);
        $this->assertNotNull($quote->cancellation_reason);
        $this->assertStringContainsString('Cancelación automática', $quote->cancellation_reason);

        // Work order should reflect cancellation
        $this->workOrder->refresh();
        $this->assertEquals('cancelada', $this->workOrder->status);
    }
}

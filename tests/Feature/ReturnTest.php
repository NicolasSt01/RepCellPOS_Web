<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\WasteRecord;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private CashRegister $cashRegister;
    private Product $product;

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

        $this->cashRegister = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $this->product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Producto Test',
            'type' => 'producto',
            'purchase_price' => 50,
            'sale_price' => 100,
            'tax_percentage' => 16,
            'stock' => 50,
            'is_active' => true,
        ]);
    }

    private function createSale(): Sale
    {
        $this->actingAs($this->user);

        $this->post(route('pos.checkout'), [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'type' => 'producto',
                    'description' => $this->product->name,
                    'quantity' => 3,
                    'unit_price' => $this->product->sale_price,
                    'tax_percentage' => $this->product->tax_percentage,
                ],
            ],
            'payment_method' => 'efectivo',
            'amount_received' => 500,
        ]);

        return Sale::where('tenant_id', $this->tenant->id)->first();
    }

    public function test_index_page_is_accessible(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('returns.index'));

        $response->assertOk();
        $response->assertSee('Devoluciones');
    }

    public function test_create_page_is_accessible(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('returns.create'));

        $response->assertOk();
        $response->assertSee('Nueva Devolución');
    }

    public function test_search_sale_by_id_returns_json(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();

        $response = $this->post(route('returns.search_sale'), [
            'folio' => (string) $sale->id,
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['found']);
        $this->assertEquals($sale->id, $data['sale']['id']);
        $this->assertFalse($data['has_return']);
        $this->assertCount(1, $data['items']);
        $this->assertEquals(3, $data['items'][0]['available_qty']);
    }

    public function test_search_sale_not_found(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('returns.search_sale'), [
            'folio' => '999999',
        ]);

        $data = $response->json();
        $this->assertFalse($data['found']);
        $this->assertEquals('Venta no encontrada', $data['message']);
    }

    public function test_search_sale_detects_existing_return(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();

        SalesReturn::create([
            'tenant_id' => $this->tenant->id,
            'sale_id' => $sale->id,
            'user_id' => $this->user->id,
            'refund_total' => 100,
            'status' => 'completada',
        ]);

        $response = $this->post(route('returns.search_sale'), [
            'folio' => (string) $sale->id,
        ]);

        $this->assertTrue($response->json('has_return'));
    }

    public function test_store_creates_return_and_restocks(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();
        $saleItem = $sale->saleItems->first();
        $originalStock = $this->product->fresh()->stock;

        $response = $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Cliente insatisfecho',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 2,
                    'refund_amount' => 200,
                    'restock' => true,
                ],
            ],
        ]);

        $response->assertRedirect(route('returns.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sales_returns', [
            'sale_id' => $sale->id,
            'reason' => 'Cliente insatisfecho',
            'refund_total' => 200,
        ]);

        $this->assertDatabaseHas('sales_return_items', [
            'sale_item_id' => $saleItem->id,
            'quantity' => 2,
            'refund_subtotal' => 200,
            'restock' => true,
        ]);

        $this->assertEquals($originalStock + 2, $this->product->fresh()->stock);

        $this->assertDatabaseHas('cash_register_movements', [
            'cash_register_id' => $this->cashRegister->id,
            'type' => 'devolucion',
            'amount' => 200,
        ]);
    }

    public function test_store_creates_waste_record_when_not_restocked(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();
        $saleItem = $sale->saleItems->first();

        $response = $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Producto dañado',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'refund_amount' => 100,
                    'restock' => false,
                    'waste_reason' => 'mal_aspecto',
                ],
            ],
        ]);

        $response->assertRedirect(route('returns.index'));

        $this->assertDatabaseHas('waste_records', [
            'quantity' => 1,
            'reason' => 'mal_aspecto',
        ]);

        $wasteRecord = WasteRecord::first();
        $this->assertNotNull($wasteRecord->sales_return_item_id);

        $this->assertEquals(47, $this->product->fresh()->stock);

        $this->assertDatabaseHas('cash_register_movements', [
            'cash_register_id' => $this->cashRegister->id,
            'type' => 'devolucion',
            'amount' => 100,
        ]);
    }

    public function test_store_rejects_quantity_exceeding_available(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();
        $saleItem = $sale->saleItems->first();

        $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Test',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'refund_amount' => 100,
                    'restock' => true,
                ],
            ],
        ]);

        $response = $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Segunda devolución',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 3,
                    'refund_amount' => 300,
                    'restock' => true,
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertStringContainsString('Cantidad excede', session('errors')->first('items'));
    }

    public function test_store_requires_waste_reason_when_not_restocked(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();
        $saleItem = $sale->saleItems->first();

        $response = $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Test',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'refund_amount' => 100,
                    'restock' => false,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.waste_reason');
    }

    public function test_index_lists_returns(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();
        $saleItem = $sale->saleItems->first();

        $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Devolución de prueba',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'refund_amount' => 100,
                    'restock' => true,
                ],
            ],
        ]);

        $response = $this->get(route('returns.index'));

        $response->assertOk();
        $response->assertSee('Devoluciones');
        $response->assertSee('$100.00');
        $response->assertSee('#' . $sale->id);
    }

    public function test_create_has_folio_query_param(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();

        $response = $this->get(route('returns.create', ['folio' => $sale->id]));

        $response->assertOk();
        $response->assertSee('Nueva Devolución');
    }

    public function test_tenant_isolation(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $this->actingAs($this->user);
        $sale = $this->createSale();

        $this->actingAs($otherUser);
        $response = $this->post(route('returns.search_sale'), [
            'folio' => (string) $sale->id,
        ]);

        $this->assertFalse($response->json('found'));
    }

    public function test_kardex_movement_created_on_restock(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();
        $saleItem = $sale->saleItems->first();

        $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Devolución',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 2,
                    'refund_amount' => 200,
                    'restock' => true,
                ],
            ],
        ]);

        $this->assertDatabaseHas('kardex_movements', [
            'product_id' => $this->product->id,
            'type' => 'entrada',
            'quantity' => 2,
        ]);
    }

    public function test_store_requires_open_cash_register(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();
        $saleItem = $sale->saleItems->first();

        $this->cashRegister->update(['status' => 'cerrada']);

        $response = $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Test sin caja',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'refund_amount' => 100,
                    'restock' => true,
                ],
            ],
        ]);

        $response->assertRedirect(route('returns.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('caja', session('error'));
    }

    public function test_cash_register_expected_cash_affected_by_return(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();
        $saleItem = $sale->saleItems->first();

        $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Devolución test',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'refund_amount' => 100,
                    'restock' => true,
                ],
            ],
        ]);

        $this->cashRegister->refresh();

        $expectedCash = $this->cashRegister->opening_amount
            + $this->cashRegister->getTotalCashSales()
            - $this->cashRegister->getTotalWithdrawals()
            - $this->cashRegister->getTotalReturns();

        $this->assertEquals($expectedCash, $this->cashRegister->getExpectedCash());
        $this->assertEquals(100, $this->cashRegister->getTotalReturns());
    }

    public function test_sale_marked_as_parcial_on_partial_return(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();
        $saleItem = $sale->saleItems->first();

        $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Devolución parcial',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'refund_amount' => 100,
                    'restock' => true,
                ],
            ],
        ]);

        $sale->refresh();

        $this->assertEquals('parcial', $sale->return_status);
        $this->assertEquals(100, (float) $sale->refunded_total);
    }

    public function test_sale_marked_as_total_on_full_return(): void
    {
        $this->actingAs($this->user);
        $sale = $this->createSale();
        $saleItem = $sale->saleItems->first();

        $expectedTotal = (float) $sale->total;

        $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Devolución total',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 3,
                    'refund_amount' => $expectedTotal,
                    'restock' => true,
                ],
            ],
        ]);

        $sale->refresh();

        $this->assertEquals('total', $sale->return_status);
        $this->assertEquals($expectedTotal, (float) $sale->refunded_total);
    }

    public function test_return_over_multiple_items_updates_sale_correctly(): void
    {
        $this->actingAs($this->user);

        $product2 = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Producto Test 2',
            'type' => 'producto',
            'purchase_price' => 30,
            'sale_price' => 60,
            'tax_percentage' => 16,
            'stock' => 20,
            'is_active' => true,
        ]);

        $this->post(route('pos.checkout'), [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'type' => 'producto',
                    'description' => $this->product->name,
                    'quantity' => 1,
                    'unit_price' => $this->product->sale_price,
                    'tax_percentage' => $this->product->tax_percentage,
                ],
                [
                    'product_id' => $product2->id,
                    'type' => 'producto',
                    'description' => $product2->name,
                    'quantity' => 2,
                    'unit_price' => $product2->sale_price,
                    'tax_percentage' => $product2->tax_percentage,
                ],
            ],
            'payment_method' => 'efectivo',
            'amount_received' => 500,
        ]);

        $sale = Sale::where('tenant_id', $this->tenant->id)->latest()->first();
        $firstItem = $sale->saleItems->first();

        $this->post(route('returns.store'), [
            'sale_id' => $sale->id,
            'reason' => 'Devolución parcial',
            'items' => [
                [
                    'sale_item_id' => $firstItem->id,
                    'quantity' => 1,
                    'refund_amount' => 116,
                    'restock' => true,
                ],
            ],
        ]);

        $sale->refresh();

        $this->assertEquals('parcial', $sale->return_status);
        $this->assertEquals(116, (float) $sale->refunded_total);
    }
}

<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PosMixedPaymentTest extends TestCase
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

        Permission::create(['guard_name' => 'web', 'name' => 'pos.access']);
        $this->user->givePermissionTo('pos.access');

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

    public function test_checkout_with_cash(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pos.checkout'), [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'type' => 'producto',
                    'description' => $this->product->name,
                    'quantity' => 1,
                    'unit_price' => $this->product->sale_price,
                    'tax_percentage' => $this->product->tax_percentage,
                ],
            ],
            'payment_method' => 'efectivo',
            'amount_received' => 150,
        ]);

        $expectedTax = 100 * 0.16;
        $expectedTotal = 100 + $expectedTax;
        $expectedChange = 150 - $expectedTotal;

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sales', [
            'subtotal' => 100,
            'tax_total' => $expectedTax,
            'total' => $expectedTotal,
            'payment_method' => 'efectivo',
            'cash_amount' => 150,
            'card_amount' => null,
            'change_amount' => $expectedChange,
        ]);
    }

    public function test_checkout_with_card(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pos.checkout'), [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'type' => 'producto',
                    'description' => $this->product->name,
                    'quantity' => 2,
                    'unit_price' => $this->product->sale_price,
                    'tax_percentage' => $this->product->tax_percentage,
                ],
            ],
            'payment_method' => 'tarjeta_transferencia',
            'payment_reference' => 'TXN-12345',
        ]);

        $expectedSubtotal = 200;
        $expectedTax = 200 * 0.16;
        $expectedTotal = $expectedSubtotal + $expectedTax;

        $response->assertRedirect(route('pos.index'));

        $this->assertDatabaseHas('sales', [
            'subtotal' => $expectedSubtotal,
            'total' => $expectedTotal,
            'payment_method' => 'tarjeta_transferencia',
            'cash_amount' => null,
            'card_amount' => $expectedTotal,
            'payment_reference' => 'TXN-12345',
            'change_amount' => 0,
        ]);
    }

    public function test_checkout_with_mixed_payment(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pos.checkout'), [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'type' => 'producto',
                    'description' => $this->product->name,
                    'quantity' => 1,
                    'unit_price' => $this->product->sale_price,
                    'tax_percentage' => $this->product->tax_percentage,
                ],
            ],
            'payment_method' => 'mixto',
            'cash_amount' => 70,
            'card_amount' => 100,
            'payment_reference' => 'TXN-67890',
        ]);

        $expectedTotal = 116;
        $expectedChange = 70 - ($expectedTotal - 100);

        $response->assertRedirect(route('pos.index'));

        $this->assertDatabaseHas('sales', [
            'total' => $expectedTotal,
            'payment_method' => 'mixto',
            'cash_amount' => 70,
            'card_amount' => 100,
            'payment_reference' => 'TXN-67890',
            'change_amount' => $expectedChange,
        ]);
    }

    public function test_mixed_payment_change_calculation(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pos.checkout'), [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'type' => 'producto',
                    'description' => $this->product->name,
                    'quantity' => 1,
                    'unit_price' => $this->product->sale_price,
                    'tax_percentage' => $this->product->tax_percentage,
                ],
            ],
            'payment_method' => 'mixto',
            'cash_amount' => 50,
            'card_amount' => 100,
            'payment_reference' => 'TXN-11111',
        ]);

        $expectedTotal = 116;
        $change = 50 - ($expectedTotal - 100);

        $this->assertDatabaseHas('sales', [
            'total' => $expectedTotal,
            'payment_method' => 'mixto',
            'cash_amount' => 50,
            'card_amount' => 100,
            'change_amount' => $change,
        ]);
    }

    public function test_cash_register_totals_with_mixed_payments(): void
    {
        $this->actingAs($this->user);

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
            ],
            'payment_method' => 'mixto',
            'cash_amount' => 30,
            'card_amount' => 86,
            'payment_reference' => 'TXN-22222',
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
            ],
            'payment_method' => 'efectivo',
            'amount_received' => 200,
        ]);

        $this->cashRegister->refresh();

        $expectedCash = 30 + 200;
        $expectedCard = 86;

        $this->assertEquals($expectedCash, $this->cashRegister->getTotalCashSales());
        $this->assertEquals($expectedCard, $this->cashRegister->getTotalCardSales());
    }

    public function test_taxes_disabled(): void
    {
        $this->tenant->update(['tax_enabled' => false]);
        $this->actingAs($this->user);

        $response = $this->post(route('pos.checkout'), [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'type' => 'producto',
                    'description' => $this->product->name,
                    'quantity' => 1,
                    'unit_price' => $this->product->sale_price,
                    'tax_percentage' => $this->product->tax_percentage,
                ],
            ],
            'payment_method' => 'efectivo',
            'amount_received' => 100,
        ]);

        $response->assertRedirect(route('pos.index'));

        $this->assertDatabaseHas('sales', [
            'subtotal' => 100,
            'tax_total' => 0,
            'total' => 100,
        ]);
    }

    public function test_checkout_requires_open_cash_register(): void
    {
        $this->cashRegister->update(['status' => 'cerrada']);
        $this->actingAs($this->user);

        $response = $this->post(route('pos.checkout'), [
            'items' => [
                [
                    'type' => 'servicio',
                    'description' => 'Servicio test',
                    'quantity' => 1,
                    'unit_price' => 50,
                    'tax_percentage' => 0,
                ],
            ],
            'payment_method' => 'efectivo',
            'amount_received' => 50,
        ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_rejects_insufficient_stock(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pos.checkout'), [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'type' => 'producto',
                    'description' => $this->product->name,
                    'quantity' => 999,
                    'unit_price' => $this->product->sale_price,
                    'tax_percentage' => $this->product->tax_percentage,
                ],
            ],
            'payment_method' => 'efectivo',
            'amount_received' => 99999,
        ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Stock insuficiente', session('error'));
    }

    public function test_checkout_rejects_aggregate_quantity_exceeding_stock(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('pos.checkout'), [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'type' => 'producto',
                    'description' => $this->product->name,
                    'quantity' => 30,
                    'unit_price' => $this->product->sale_price,
                    'tax_percentage' => $this->product->tax_percentage,
                ],
                [
                    'product_id' => $this->product->id,
                    'type' => 'producto',
                    'description' => $this->product->name,
                    'quantity' => 30,
                    'unit_price' => $this->product->sale_price,
                    'tax_percentage' => $this->product->tax_percentage,
                ],
            ],
            'payment_method' => 'efectivo',
            'amount_received' => 99999,
        ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Stock insuficiente', session('error'));
    }

    public function test_print_route_returns_view(): void
    {
        $this->actingAs($this->user);

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
            ],
            'payment_method' => 'efectivo',
            'amount_received' => 116,
        ]);

        $sale = Sale::where('tenant_id', $this->tenant->id)->first();

        $response = $this->get(route('pos.print', $sale));
        $response->assertOk();
        $response->assertSee('Ticket #' . $sale->id);
    }
}

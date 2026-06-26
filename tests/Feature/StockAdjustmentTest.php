<?php

namespace Tests\Feature;

use App\Models\KardexMovement;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $token = Str::random(60);
        $this->user->update(['session_token' => $token]);
        session(['session_token' => $token]);

        $this->actingAs($this->user);

        $this->product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Producto Inventario',
            'type' => 'producto',
            'purchase_price' => 50,
            'sale_price' => 100,
            'stock' => 20,
            'min_stock' => 5,
            'is_active' => true,
        ]);
    }

    public function test_entrada_increases_stock_and_creates_kardex(): void
    {
        $response = $this->post(route('products.adjust_stock', $this->product), [
            'quantity' => 10,
            'type' => 'entrada',
            'notes' => 'Compra a proveedor',
        ]);

        $response->assertRedirect(route('products.show', $this->product));
        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals(30, $this->product->stock);

        $this->assertDatabaseHas('kardex_movements', [
            'product_id' => $this->product->id,
            'type' => 'entrada',
            'quantity' => 10,
            'previous_stock' => 20,
            'resulting_stock' => 30,
            'notes' => 'Compra a proveedor',
        ]);
    }

    public function test_salida_decreases_stock_and_creates_kardex(): void
    {
        $response = $this->post(route('products.adjust_stock', $this->product), [
            'quantity' => 5,
            'type' => 'salida',
            'notes' => 'Salida por garantía',
        ]);

        $response->assertRedirect(route('products.show', $this->product));
        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals(15, $this->product->stock);

        $this->assertDatabaseHas('kardex_movements', [
            'product_id' => $this->product->id,
            'type' => 'salida',
            'quantity' => 5,
            'previous_stock' => 20,
            'resulting_stock' => 15,
            'notes' => 'Salida por garantía',
        ]);
    }

    public function test_ajuste_sets_stock_to_new_value(): void
    {
        $response = $this->post(route('products.adjust_stock', $this->product), [
            'quantity' => 8,
            'type' => 'ajuste',
            'notes' => 'Ajuste por inventario físico',
        ]);

        $response->assertRedirect(route('products.show', $this->product));
        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals(8, $this->product->stock);

        $this->assertDatabaseHas('kardex_movements', [
            'product_id' => $this->product->id,
            'type' => 'ajuste',
            'previous_stock' => 20,
            'resulting_stock' => 8,
            'notes' => 'Ajuste por inventario físico',
        ]);
    }

    public function test_kardex_movement_records_user(): void
    {
        $this->post(route('products.adjust_stock', $this->product), [
            'quantity' => 5,
            'type' => 'entrada',
        ]);

        $this->assertDatabaseHas('kardex_movements', [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_show_page_displays_kardex_history(): void
    {
        $this->post(route('products.adjust_stock', $this->product), [
            'quantity' => 5,
            'type' => 'entrada',
            'notes' => 'Primera entrada',
        ]);

        $this->post(route('products.adjust_stock', $this->product), [
            'quantity' => 3,
            'type' => 'salida',
            'notes' => 'Salida menor',
        ]);

        $response = $this->get(route('products.show', $this->product));
        $response->assertOk();
        $response->assertSee('Entrada');
        $response->assertSee('Salida');
        // Verify quantities 5 and 3 appear (entrada and salida)
        $response->assertSeeInOrder(['5', '20', '25', '3', '25', '22']);
    }

    public function test_can_adjust_stock_on_service(): void
    {
        $service = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Servicio Test',
            'type' => 'servicio',
            'purchase_price' => 0,
            'sale_price' => 100,
            'stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->post(route('products.adjust_stock', $service), [
            'quantity' => 5,
            'type' => 'entrada',
        ]);

        $response->assertRedirect(route('products.show', $service));
        $service->refresh();
        $this->assertEquals(5, $service->stock);
    }

    public function test_kardex_movement_reference_polymorphic(): void
    {
        $this->post(route('products.adjust_stock', $this->product), [
            'quantity' => 5,
            'type' => 'entrada',
        ]);

        $movement = KardexMovement::where('product_id', $this->product->id)->first();
        $this->assertNull($movement->reference_type);
        $this->assertNull($movement->reference_id);
    }
}

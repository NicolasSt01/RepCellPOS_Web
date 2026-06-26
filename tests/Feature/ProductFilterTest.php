<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Category $category;

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

        $this->category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pantallas',
        ]);
    }

    public function test_index_lists_all_products(): void
    {
        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Producto A',
            'type' => 'producto',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Producto B',
            'type' => 'producto',
            'purchase_price' => 15,
            'sale_price' => 30,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index'));
        $response->assertOk();
        $response->assertSee('Producto A');
        $response->assertSee('Producto B');
    }

    public function test_filter_by_search(): void
    {
        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pantalla iPhone 13',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Batería Samsung',
            'type' => 'producto',
            'purchase_price' => 50,
            'sale_price' => 120,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index', ['search' => 'iPhone']));
        $response->assertOk();
        $response->assertSee('Pantalla iPhone 13');
        $response->assertDontSee('Batería Samsung');
    }

    public function test_filter_by_search_on_code(): void
    {
        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Producto con código',
            'code' => 'COD-123',
            'type' => 'producto',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index', ['search' => 'COD-123']));
        $response->assertOk();
        $response->assertSee('Producto con código');
    }

    public function test_filter_by_search_on_barcode(): void
    {
        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Producto con barcode',
            'barcode' => 'BARCODE-XYZ',
            'type' => 'producto',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index', ['search' => 'BARCODE-XYZ']));
        $response->assertOk();
        $response->assertSee('Producto con barcode');
    }

    public function test_filter_by_category(): void
    {
        $otherCategory = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Baterías',
        ]);

        Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Producto Pantalla',
            'type' => 'producto',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $otherCategory->id,
            'name' => 'Producto Batería',
            'type' => 'producto',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index', ['category' => $this->category->id]));
        $response->assertOk();
        $response->assertSee('Producto Pantalla');
        $response->assertDontSee('Producto Batería');
    }

    public function test_filter_by_type(): void
    {
        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Producto Físico',
            'type' => 'producto',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Servicio Técnico',
            'type' => 'servicio',
            'purchase_price' => 0,
            'sale_price' => 100,
            'stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index', ['type' => 'servicio']));
        $response->assertOk();
        $response->assertSee('Servicio Técnico');
        $response->assertDontSee('Producto Físico');
    }

    public function test_filter_low_stock_products(): void
    {
        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Stock Bajo',
            'type' => 'producto',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock' => 3,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Stock Suficiente',
            'type' => 'producto',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock' => 20,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index', ['low_stock' => 1]));
        $response->assertOk();
        $response->assertSee('Stock Bajo');
        $response->assertDontSee('Stock Suficiente');
    }

    public function test_filter_by_part_number(): void
    {
        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Refacción específica',
            'part_number' => 'PART-A1B2C3',
            'type' => 'producto',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index', ['search' => 'PART-A1B2C3']));
        $response->assertOk();
        $response->assertSee('Refacción específica');
    }

    public function test_service_type_shows_in_index(): void
    {
        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Mano de Obra',
            'type' => 'servicio',
            'purchase_price' => 0,
            'sale_price' => 150,
            'stock' => 0,
            'min_stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index'));
        $response->assertOk();
        $response->assertSee('Mano de Obra');
    }
}

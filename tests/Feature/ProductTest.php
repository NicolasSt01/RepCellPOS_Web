<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        Storage::fake('r2');
    }

    public function test_can_create_product_with_image(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'name' => 'Pantalla iPhone 13',
            'type' => 'producto',
            'purchase_price' => 1500,
            'sale_price' => 2500,
            'stock' => 5,
            'min_stock' => 1,
            'is_active' => true,
            'image' => UploadedFile::fake()->image('pantalla.jpg'),
        ]);

        $response->assertSessionHas('success');

        $product = Product::where('name', 'Pantalla iPhone 13')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image_url);
        $this->assertStringContainsString('products/', $product->image_url);

        Storage::disk('r2')->assertExists($product->image_url);
    }

    public function test_can_update_product_image(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 10,
            'is_active' => true,
            'image_url' => 'products/old/image.jpg',
        ]);

        Storage::disk('r2')->put('products/old/image.jpg', 'fake-content');

        $response = $this->actingAs($this->user)->put(route('products.update', $product), [
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 10,
            'is_active' => true,
            'image' => UploadedFile::fake()->image('nueva.jpg'),
        ]);

        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertNotNull($product->image_url);
        $this->assertStringContainsString('products/', $product->image_url);
        $this->assertStringNotContainsString('old/image.jpg', $product->image_url);

        Storage::disk('r2')->assertMissing('products/old/image.jpg');
    }

    public function test_product_view_includes_image_url(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bocina',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 10,
            'is_active' => true,
            'image_url' => 'products/test/img.jpg',
        ]);

        $response = $this->actingAs($this->user)->get(route('products.show', $product));

        $response->assertOk();
        $response->assertSee($product->getImageUrl());
    }
}

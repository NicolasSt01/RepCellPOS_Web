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

    public function test_can_create_product_with_barcode_generated_flag(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'name' => 'Batería iPhone X',
            'type' => 'producto',
            'purchase_price' => 200,
            'sale_price' => 450,
            'stock' => 10,
            'barcode' => 'INTABC123',
            'barcode_generated' => '1',
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('barcode_label');

        $product = Product::where('name', 'Batería iPhone X')->first();
        $this->assertNotNull($product);
        $this->assertEquals('INTABC123', $product->barcode);
    }

    public function test_can_create_product_without_barcode_generated(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'name' => 'Funda iPhone 12',
            'type' => 'producto',
            'purchase_price' => 50,
            'sale_price' => 120,
            'stock' => 25,
            'barcode' => '871234567890',
            'barcode_generated' => '0',
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionMissing('barcode_label');

        $product = Product::where('name', 'Funda iPhone 12')->first();
        $this->assertNotNull($product);
        $this->assertEquals('871234567890', $product->barcode);
    }

    public function test_can_create_product_without_barcode_field(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'name' => 'Service Pantalla',
            'type' => 'servicio',
            'purchase_price' => 100,
            'sale_price' => 300,
        ]);

        $response->assertSessionHas('success');

        $product = Product::where('name', 'Service Pantalla')->first();
        $this->assertNotNull($product);
        $this->assertNull($product->barcode);
    }

    public function test_product_print_labels_returns_pdf(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Prueba Labels',
            'type' => 'producto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 5,
            'barcode' => 'INTTEST001',
        ]);

        $response = $this->actingAs($this->user)->post(route('products.print_labels', $product), [
            'quantity' => 3,
        ]);

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
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

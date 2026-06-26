<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

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
    }

    public function test_index_lists_categories(): void
    {
        Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pantallas',
            'description' => 'Pantallas para celulares',
        ]);

        $response = $this->get(route('categories.index'));
        $response->assertOk();
        $response->assertSee('Pantallas');
    }

    public function test_create_category(): void
    {
        $response = $this->post(route('categories.store'), [
            'name' => 'Baterías',
            'description' => 'Baterías y cargadores',
        ]);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Baterías',
            'is_active' => true,
        ]);
    }

    public function test_update_category(): void
    {
        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Viejo Nombre',
        ]);

        $response = $this->put(route('categories.update', $category), [
            'name' => 'Nuevo Nombre',
            'description' => 'Descripción actualizada',
            'is_active' => false,
        ]);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');

        $category->refresh();
        $this->assertEquals('Nuevo Nombre', $category->name);
        $this->assertFalse($category->is_active);
    }

    public function test_delete_category_without_products(): void
    {
        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Eliminable',
        ]);

        $response = $this->delete(route('categories.destroy', $category));
        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Con productos',
        ]);

        Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Producto asociado',
            'type' => 'producto',
            'purchase_price' => 10,
            'sale_price' => 20,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->delete(route('categories.destroy', $category));
        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_create_category_requires_name(): void
    {
        $response = $this->post(route('categories.store'), []);
        $response->assertSessionHasErrors('name');
    }
}

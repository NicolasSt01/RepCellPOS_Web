<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::create([
            'name' => 'Unlimited',
            'slug' => 'unlimited',
            'price' => 0,
            'features' => ['work_orders' => true, 'quotes' => true, 'pos' => true, 'notifications_email' => true, 'notifications_whatsapp' => true],
            'limits' => ['max_users' => -1, 'max_clients' => -1, 'max_monthly_work_orders' => -1, 'storage_mb' => -1],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::factory()->create([
            'plan_id' => $plan->id,
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        
        Storage::fake('r2');
    }

    public function test_can_view_create_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('work_orders.create'));
        $response->assertOk();
        $response->assertSee('Nueva Orden');
        $response->assertSee('x-data="multiStepForm()"', false);
        $response->assertSee('Información del Cliente');
    }

    public function test_can_search_clients_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('work_orders.search_clients', ['q' => substr($this->client->name, 0, 3)]));
        $response->assertOk();
        $response->assertJsonFragment(['id' => $this->client->id]);
    }

    public function test_can_store_client_inline_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('work_orders.store_client'), [
            'name' => 'John Doe Inline',
            'phone' => '1234567890',
            'email' => 'john@test.com',
            'notification_preference' => 'whatsapp',
        ]);
        $response->assertOk();
        $response->assertJsonFragment(['name' => 'John Doe Inline']);
        
        $this->assertDatabaseHas('clients', [
            'name' => 'John Doe Inline',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_can_create_work_order_with_multistep_data(): void
    {
        $response = $this->actingAs($this->user)->post(route('work_orders.store'), [
            'client_id' => $this->client->id,
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'device_serial' => 'SN123456',
            'device_imei' => 'IMEI123456',
            'unlock_pattern' => '12345',
            'unlock_pin' => '0000',
            'problem_description' => 'Pantalla rota',
            'images' => [
                UploadedFile::fake()->image('photo1.jpg')
            ]
        ]);

        $workOrder = WorkOrder::where('device_brand', 'Apple')->first();
        $this->assertNotNull($workOrder);
        
        $response->assertRedirect(route('work_orders.print', $workOrder));
        $response->assertSessionHas('success');

        $this->assertEquals('Apple', $workOrder->device_brand);
        $this->assertEquals('iPhone 13', $workOrder->device_model);
        $this->assertEquals('12345', $workOrder->unlock_pattern);

        $workOrder->refresh();
        $this->assertEquals('en_espera', $workOrder->status);
        $this->assertNotNull($workOrder->images);
        $this->assertCount(1, $workOrder->images);
    }

    public function test_can_add_images_to_existing_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'images' => ['work_orders/2026/06/old-photo.jpg'],
        ]);

        $response = $this->actingAs($this->user)->post(route('work_orders.images.store', $workOrder), [
            'images' => [
                UploadedFile::fake()->image('new1.jpg'),
                UploadedFile::fake()->image('new2.png'),
            ],
        ]);

        $response->assertRedirect(route('work_orders.show', $workOrder));
        $response->assertSessionHas('success');

        $workOrder->refresh();
        $this->assertCount(3, $workOrder->images);
        $this->assertEquals('work_orders/2026/06/old-photo.jpg', $workOrder->images[0]);

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
        ]);

        $events = $workOrder->timeline;
        $lastEvent = end($events);
        $this->assertStringContainsString('2 foto(s)', $lastEvent['comentario']);
        $this->assertEquals($this->user->name, $lastEvent['usuario']);
    }

    public function test_add_images_validates_max_five(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
        ]);

        $images = [];
        for ($i = 0; $i < 6; $i++) {
            $images[] = UploadedFile::fake()->image("photo{$i}.jpg");
        }

        $response = $this->actingAs($this->user)->post(route('work_orders.images.store', $workOrder), [
            'images' => $images,
        ]);

        $response->assertSessionHasErrors('images');
    }

    public function test_add_images_requires_images(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('work_orders.images.store', $workOrder), [
            'images' => [],
        ]);

        $response->assertSessionHasErrors('images');
    }

    public function test_add_images_merges_with_existing_empty_images(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'images' => null,
        ]);

        $response = $this->actingAs($this->user)->post(route('work_orders.images.store', $workOrder), [
            'images' => [
                UploadedFile::fake()->image('first.jpg'),
            ],
        ]);

        $response->assertRedirect(route('work_orders.show', $workOrder));
        $response->assertSessionHas('success');

        $workOrder->refresh();
        $this->assertCount(1, $workOrder->images);
    }

    public function test_show_page_displays_image_gallery(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'images' => ['work_orders/test/photo1.jpg'],
        ]);

        $response = $this->actingAs($this->user)->get(route('work_orders.show', $workOrder));

        $response->assertOk();
        $response->assertSee('Fotos del Equipo');
        $response->assertSee('Agregar más fotos');
        $response->assertSee(route('r2.serve', ['path' => 'work_orders/test/photo1.jpg']), false);
    }

    public function test_show_page_shows_upload_form_when_no_images(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'images' => null,
        ]);

        $response = $this->actingAs($this->user)->get(route('work_orders.show', $workOrder));

        $response->assertOk();
        $response->assertSee('Fotos del Equipo');
        $response->assertSee('Aún no se han agregado fotos');
        $response->assertSee('Agregar más fotos');
    }
}

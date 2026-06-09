<?php

namespace Tests\Feature;

use App\Models\Client;
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

        $this->tenant = Tenant::factory()->create();

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
            // Simulating image upload
            'images' => [
                UploadedFile::fake()->image('photo1.jpg')
            ]
        ]);

        $workOrder = WorkOrder::where('device_brand', 'Apple')->first();
        $this->assertNotNull($workOrder);
        
        $response->assertRedirect(route('work_orders.show', $workOrder));
        $response->assertSessionHas('success');

        $this->assertEquals('Apple', $workOrder->device_brand);
        $this->assertEquals('iPhone 13', $workOrder->device_model);
        $this->assertEquals('12345', $workOrder->unlock_pattern);

        $workOrder->refresh();
        $this->assertEquals('en_espera', $workOrder->status);
        $this->assertNotNull($workOrder->images);
        $this->assertCount(1, $workOrder->images);
    }
}

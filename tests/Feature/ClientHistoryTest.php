<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientHistoryTest extends TestCase
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
    }

    public function test_client_show_page_renders_work_orders_table(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('clients.show', $this->client));

        $response->assertOk();
        $response->assertSee('Historial de Órdenes de Trabajo');
        $response->assertSee($workOrder->work_order_number);
        $response->assertSee($workOrder->device_brand);
        $response->assertSee($workOrder->device_model);
    }

    public function test_client_show_page_shows_work_order_number(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'work_order_number' => 'WO-99999',
        ]);

        $response = $this->actingAs($this->user)->get(route('clients.show', $this->client));

        $response->assertOk();
        $response->assertSee('WO-99999');
    }

    public function test_client_show_page_paginates_work_orders(): void
    {
        WorkOrder::factory(12)->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('clients.show', $this->client));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Número', 'Equipo', 'Estado', 'Prioridad', 'Técnico', 'Fecha', 'Acciones']);
        $response->assertDontSee('No hay órdenes de trabajo registradas para este cliente.');

        $response->assertSee('Showing');
        $response->assertSee('12');
        $response->assertSee('pagination.next');

        $responsePage2 = $this->actingAs($this->user)->get(route('clients.show', [$this->client, 'page' => 2]));
        $responsePage2->assertOk();
        $responsePage2->assertSee('results');
    }
}

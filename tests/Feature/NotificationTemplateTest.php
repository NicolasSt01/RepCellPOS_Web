<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\NotificationTemplate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTemplateTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_can_create_notification_template(): void
    {
        $template = NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'subject' => 'Tu cotización',
            'body' => 'Hola {client_name}, tu orden {work_order_number} está lista.',
            'is_active' => true,
        ]);

        $this->assertNotNull($template);
        $this->assertEquals('quote_sent', $template->event);
        $this->assertEquals('email', $template->channel);
    }

    public function test_unique_constraint_on_tenant_event_channel(): void
    {
        NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'First template',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'Duplicate template',
        ]);
    }

    public function test_get_template_returns_active_template(): void
    {
        NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'Active template',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::getTemplate($this->tenant->id, 'quote_sent', 'email');

        $this->assertNotNull($template);
        $this->assertEquals('Active template', $template->body);
    }

    public function test_get_template_ignores_inactive(): void
    {
        NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'Inactive template',
            'is_active' => false,
        ]);

        $template = NotificationTemplate::getTemplate($this->tenant->id, 'quote_sent', 'email');

        $this->assertNull($template);
    }

    public function test_get_template_returns_null_when_no_match(): void
    {
        $template = NotificationTemplate::getTemplate($this->tenant->id, 'nonexistent', 'email');

        $this->assertNull($template);
    }

    public function test_get_template_scoped_to_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();

        NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'Tenant 1 template',
            'is_active' => true,
        ]);

        NotificationTemplate::create([
            'tenant_id' => $otherTenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'Tenant 2 template',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::getTemplate($this->tenant->id, 'quote_sent', 'email');

        $this->assertNotNull($template);
        $this->assertEquals('Tenant 1 template', $template->body);
    }

    public function test_replace_placeholders_work_order_number(): void
    {
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
        ]);

        $template = NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'Orden: {work_order_number}',
            'is_active' => true,
        ]);

        $result = $template->replacePlaceholders($workOrder);

        $this->assertStringContainsString($workOrder->work_order_number, $result);
        $this->assertStringNotContainsString('{work_order_number}', $result);
    }

    public function test_replace_placeholders_client_name(): void
    {
        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Juan Pérez',
        ]);

        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
        ]);

        $template = NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'Hola {client_name}',
            'is_active' => true,
        ]);

        $result = $template->replacePlaceholders($workOrder);

        $this->assertStringContainsString('Juan Pérez', $result);
    }

    public function test_replace_placeholders_tracking_url(): void
    {
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
            'tracking_token' => 'test-token-abc',
        ]);

        $template = NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'Sigue aquí: {tracking_url}',
            'is_active' => true,
        ]);

        $result = $template->replacePlaceholders($workOrder);

        $this->assertStringContainsString('/seguimiento/test-token-abc', $result);
    }

    public function test_update_or_create_creates_template(): void
    {
        \App\Models\NotificationTemplate::updateOrCreate(
            [
                'tenant_id' => $this->tenant->id,
                'event' => 'order_created',
                'channel' => 'email',
            ],
            [
                'subject' => 'Orden recibida',
                'body' => 'Hola {client_name}, tu orden {work_order_number} fue recibida.',
                'is_active' => true,
            ]
        );

        $this->assertDatabaseHas('notification_templates', [
            'tenant_id' => $this->tenant->id,
            'event' => 'order_created',
            'channel' => 'email',
            'subject' => 'Orden recibida',
            'body' => 'Hola {client_name}, tu orden {work_order_number} fue recibida.',
            'is_active' => true,
        ]);
    }

    public function test_update_or_create_updates_existing_template(): void
    {
        NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'order_created',
            'channel' => 'email',
            'body' => 'Original',
            'is_active' => true,
        ]);

        NotificationTemplate::updateOrCreate(
            [
                'tenant_id' => $this->tenant->id,
                'event' => 'order_created',
                'channel' => 'email',
            ],
            [
                'subject' => 'Updated',
                'body' => 'Updated body',
                'is_active' => true,
            ]
        );

        $this->assertEquals(
            1,
            NotificationTemplate::where('tenant_id', $this->tenant->id)
                ->where('event', 'order_created')
                ->where('channel', 'email')
                ->count()
        );

        $template = NotificationTemplate::where('tenant_id', $this->tenant->id)
            ->where('event', 'order_created')
            ->where('channel', 'email')
            ->first();

        $this->assertEquals('Updated body', $template->body);
    }
}

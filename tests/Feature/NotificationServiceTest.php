<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\EvolutionApiService;
use App\Services\NotificationService;
use App\Services\TenantMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private WorkOrder $workOrder;
    private Client $client;
    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->tenant = Tenant::factory()->create([
            'mail_host' => 'smtp.test.com',
            'mail_port' => '587',
            'mail_username' => 'test@test.com',
            'mail_password' => 'password',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'test@test.com',
            'mail_from_name' => 'Test',
            'whatsapp_webhook_url' => 'https://n8n.test/webhook/whatsapp',
        ]);

        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'client@test.com',
            'phone' => '5551234567',
            'notification_preference' => 'email',
        ]);

        $this->workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'status' => 'diagnosticada',
        ]);

        $this->service = app(NotificationService::class);
    }

    public function test_send_email_with_proper_smtp_config(): void
    {
        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('email', $notification->channel);
        $this->assertEquals('quote_sent', $notification->event);
        $this->assertEquals('sent', $notification->status);
    }

    public function test_send_email_when_smtp_not_configured_marks_logged(): void
    {
        $this->tenant->update([
            'mail_host' => null,
            'mail_username' => null,
            'mail_password' => null,
        ]);

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('logged', $notification->status);
        $this->assertEquals('SMTP no configurado', $notification->response);
    }

    public function test_send_email_when_client_has_no_email_marks_failed(): void
    {
        $this->client->update(['email' => null]);

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('failed', $notification->status);
        $this->assertEquals('Cliente sin email', $notification->response);
    }

    public function test_send_whatsapp_when_not_configured_falls_back_to_email(): void
    {
        $this->client->update(['notification_preference' => 'whatsapp']);

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('email', $notification->channel);
        $this->assertEquals('sent', $notification->status);
    }

    public function test_send_whatsapp_when_not_configured_and_no_email_marks_logged(): void
    {
        $this->client->update(['notification_preference' => 'whatsapp', 'email' => null]);

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('logged', $notification->status);
    }

    public function test_send_whatsapp_with_evolution_api_configured(): void
    {
        config(['services.n8n.modulacion_whatsapp_url' => null]);

        $plan = \App\Models\Plan::create([
            'name' => 'Premium Test',
            'slug' => 'premium-test',
            'description' => 'Test plan',
            'price' => 499,
            'features' => ['notifications_whatsapp' => true],
            'limits' => ['max_users' => 5, 'max_clients' => -1, 'max_monthly_work_orders' => -1, 'storage_mb' => 100],
        ]);

        $this->client->update(['notification_preference' => 'whatsapp']);
        $this->tenant->update([
            'plan_id' => $plan->id,
            'configuracion' => [
                'evolution_api' => [
                    'instance' => 'tenant_test',
                    'connected' => true,
                    'connected_at' => now()->toDateTimeString(),
                    'whatsapp_plantilla' => 'breve',
                ],
                'limites' => ['funcionalidades' => ['whatsapp_mes' => 1000, 'whatsapp_mes_count' => 0, 'whatsapp_mes_periodo' => now()->format('Y-m')]],
            ],
        ]);

        $this->mock(EvolutionApiService::class, function ($mock) {
            $mock->shouldReceive('sendText')->once()->andReturn(['_status' => 200]);
        });

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('sent', $notification->status);
    }

    public function test_whatsapp_strips_non_numeric_phone(): void
    {
        config(['services.n8n.modulacion_whatsapp_url' => null]);

        $plan = \App\Models\Plan::create([
            'name' => 'Premium Test',
            'slug' => 'premium-test',
            'description' => 'Test plan',
            'price' => 499,
            'features' => ['notifications_whatsapp' => true],
            'limits' => ['max_users' => 5, 'max_clients' => -1, 'max_monthly_work_orders' => -1, 'storage_mb' => 100],
        ]);

        $this->client->update([
            'notification_preference' => 'whatsapp',
            'phone' => '+52 (555) 123-4567',
        ]);
        $this->tenant->update([
            'plan_id' => $plan->id,
            'configuracion' => [
                'evolution_api' => [
                    'instance' => 'tenant_test',
                    'connected' => true,
                    'whatsapp_plantilla' => 'breve',
                ],
                'limites' => ['funcionalidades' => ['whatsapp_mes' => 1000, 'whatsapp_mes_count' => 0, 'whatsapp_mes_periodo' => now()->format('Y-m')]],
            ],
        ]);

        $this->mock(EvolutionApiService::class, function ($mock) {
            $mock->shouldReceive('sendText')->once()->andReturn(['_status' => 200]);
        });

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertEquals('sent', $notification->status);
    }

    public function test_whatsapp_fails_with_empty_phone(): void
    {
        $plan = \App\Models\Plan::create([
            'name' => 'Premium Test',
            'slug' => 'premium-test',
            'description' => 'Test plan',
            'price' => 499,
            'features' => ['notifications_whatsapp' => true],
            'limits' => ['max_users' => 5, 'max_clients' => -1, 'max_monthly_work_orders' => -1, 'storage_mb' => 100],
        ]);

        $this->client->update([
            'notification_preference' => 'whatsapp',
            'phone' => '',
        ]);
        $this->tenant->update([
            'plan_id' => $plan->id,
            'configuracion' => [
                'evolution_api' => [
                    'instance' => 'tenant_test',
                    'connected' => true,
                    'whatsapp_plantilla' => 'breve',
                ],
                'limites' => ['funcionalidades' => ['whatsapp_mes' => 1000, 'whatsapp_mes_count' => 0, 'whatsapp_mes_periodo' => now()->format('Y-m')]],
            ],
        ]);

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertEquals('failed', $notification->status);
        $this->assertEquals('Cliente sin teléfono válido', $notification->response);
    }

    public function test_template_fallback_to_default_when_no_template_exists(): void
    {
        $this->assertNull(NotificationTemplate::getTemplate($this->tenant->id, 'quote_sent', 'email'));

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertStringContainsString('cotización', $notification->message);
    }

    public function test_custom_template_is_used_when_exists(): void
    {
        NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'Hola {client_name}, tu orden {work_order_number} está lista. {tracking_url}',
            'is_active' => true,
        ]);

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertStringContainsString('Hola', $notification->message);
        $this->assertStringContainsString($this->workOrder->work_order_number, $notification->message);
        $this->assertStringContainsString($this->client->name, $notification->message);
    }

    public function test_call_channel_marks_as_logged(): void
    {
        $this->client->update(['notification_preference' => 'call']);

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('logged', $notification->status);
    }

    public function test_send_returns_null_when_no_client(): void
    {
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $wo = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
        ]);
        $client->delete();

        $result = $this->service->send($wo->fresh(), 'quote_sent');

        $this->assertNull($result);
    }

    public function test_custom_message_overrides_template(): void
    {
        NotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'event' => 'quote_sent',
            'channel' => 'email',
            'body' => 'Template body',
            'is_active' => true,
        ]);

        $notification = $this->service->send($this->workOrder, 'quote_sent', 'Custom message');

        $this->assertEquals('Custom message', $notification->message);
    }
}

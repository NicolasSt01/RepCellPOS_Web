<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
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
            'mail_password' => encrypt('password'),
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

    public function test_send_whatsapp_when_webhook_not_configured_marks_logged(): void
    {
        $this->client->update(['notification_preference' => 'whatsapp']);
        $this->tenant->update(['whatsapp_webhook_url' => null]);

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('logged', $notification->status);
        $this->assertEquals('WhatsApp webhook no configurado', $notification->response);
    }

    public function test_send_whatsapp_with_webhook_configured(): void
    {
        Http::fake([
            'https://n8n.test/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $this->client->update(['notification_preference' => 'whatsapp']);

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('sent', $notification->status);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://n8n.test/webhook/whatsapp'
                && $request['phone'] === '5551234567'
                && $request['work_order_id'] === $this->workOrder->id
                && $request['event'] === 'quote_sent';
        });
    }

    public function test_whatsapp_strips_non_numeric_phone(): void
    {
        Http::fake();

        $this->client->update([
            'notification_preference' => 'whatsapp',
            'phone' => '+52 (555) 123-4567',
        ]);

        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertEquals('sent', $notification->status);

        Http::assertSent(function ($request) {
            return $request['phone'] === '525551234567';
        });
    }

    public function test_whatsapp_fails_with_empty_phone(): void
    {
        $this->client->update([
            'notification_preference' => 'whatsapp',
            'phone' => '',
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

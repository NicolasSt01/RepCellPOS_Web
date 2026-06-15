<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class QuoteNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private WorkOrder $workOrder;
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
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'client@test.com',
            'notification_preference' => 'email',
        ]);

        $this->workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
            'status' => 'diagnosticada',
        ]);

        $this->service = app(NotificationService::class);
    }

    public function test_send_quote_creates_notification_record(): void
    {
        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertDatabaseHas('notifications', [
            'work_order_id' => $this->workOrder->id,
            'event' => 'quote_sent',
            'client_id' => $this->workOrder->client_id,
        ]);
    }

    public function test_send_quote_notification_has_correct_event(): void
    {
        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('quote_sent', $notification->event);
        $this->assertEquals('email', $notification->channel);
    }

    public function test_send_quote_notification_status_is_sent(): void
    {
        $notification = $this->service->send($this->workOrder, 'quote_sent');

        $this->assertNotNull($notification);
        $this->assertEquals('sent', $notification->status);
    }

    public function test_send_quote_handles_no_smtp_gracefully(): void
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
}

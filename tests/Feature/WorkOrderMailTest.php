<?php

namespace Tests\Feature;

use App\Mail\WorkOrderReceipt;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkOrderMailTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

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

        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($this->user);
    }

    public function test_sends_email_when_client_prefers_email(): void
    {
        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'notification_preference' => 'email',
            'email' => 'client@test.com',
        ]);

        $this->post(route('work_orders.store'), [
            'client_id' => $client->id,
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'problem_description' => 'Pantalla rota',
        ]);

        $workOrder = WorkOrder::where('tenant_id', $this->tenant->id)->latest()->first();
        Mail::assertSent(WorkOrderReceipt::class, function ($mail) use ($workOrder, $client) {
            return $mail->hasTo($client->email)
                && $mail->workOrder->id === $workOrder->id;
        });
    }

    public function test_does_not_send_email_when_client_prefers_whatsapp(): void
    {
        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'notification_preference' => 'whatsapp',
        ]);

        $this->post(route('work_orders.store'), [
            'client_id' => $client->id,
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'problem_description' => 'Pantalla rota',
        ]);

        Mail::assertNothingSent();
    }

    public function test_does_not_send_email_when_client_has_no_email(): void
    {
        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'notification_preference' => 'email',
            'email' => null,
        ]);

        $this->post(route('work_orders.store'), [
            'client_id' => $client->id,
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'problem_description' => 'Pantalla rota',
        ]);

        Mail::assertNothingSent();
    }

    public function test_does_not_send_email_when_tenant_has_no_smtp(): void
    {
        $this->tenant->update([
            'mail_host' => null,
            'mail_username' => null,
            'mail_password' => null,
        ]);

        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'notification_preference' => 'email',
            'email' => 'client@test.com',
        ]);

        $this->post(route('work_orders.store'), [
            'client_id' => $client->id,
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'problem_description' => 'Pantalla rota',
        ]);

        Mail::assertNothingSent();
    }

    public function test_work_order_receipt_mailable_has_correct_subject(): void
    {
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
        ]);

        $workOrder->tracking_token = 'test-token-123';
        $workOrder->save();

        $mailable = new WorkOrderReceipt($workOrder, $this->tenant);

        $mailable->assertSeeInHtml($workOrder->work_order_number);
        $mailable->assertSeeInHtml($workOrder->client->name);
        $mailable->assertSeeInHtml($workOrder->device_brand);
    }
}

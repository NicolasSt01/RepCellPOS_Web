<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Quote;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotePdfTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Quote $quote;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create([
            'tax_enabled' => true,
            'tax_percentage' => 16,
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
            'status' => 'diagnosticada',
        ]);

        $this->quote = Quote::factory()->create([
            'tenant_id' => $this->tenant->id,
            'work_order_id' => $workOrder->id,
            'status' => 'pendiente',
            'subtotal' => 1000,
            'tax_total' => 160,
            'total' => 1160,
        ]);
    }

    public function test_pdf_route_returns_pdf(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('quotes.pdf', $this->quote));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            "cotizacion-{$this->quote->workOrder->work_order_number}.pdf",
            $response->headers->get('Content-Disposition')
        );
    }

    public function test_pdf_route_denied_for_other_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('quotes.pdf', $this->quote));

        $response->assertForbidden();
    }

    public function test_download_button_present_on_show_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('quotes.show', $this->quote->workOrder));

        $response->assertOk();
        $response->assertSee('Descargar PDF');
        $response->assertSee(route('quotes.pdf', $this->quote));
    }
}

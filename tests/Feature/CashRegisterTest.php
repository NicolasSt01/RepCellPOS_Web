<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\CashRegisterIncident;
use App\Models\CashRegisterMovement;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CashRegisterTest extends TestCase
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

    public function test_index_shows_no_open_register(): void
    {
        $response = $this->get(route('cash_registers.index'));
        $response->assertOk();
    }

    public function test_open_register_successfully(): void
    {
        $response = $this->post(route('cash_registers.open'), [
            'opening_amount' => 1000,
        ]);

        $response->assertRedirect(route('cash_registers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cash_registers', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 1000,
            'status' => 'abierta',
        ]);
    }

    public function test_cannot_open_register_when_one_is_already_open(): void
    {
        CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $response = $this->post(route('cash_registers.open'), [
            'opening_amount' => 1000,
        ]);

        $response->assertRedirect(route('cash_registers.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Ya hay una caja abierta', session('error'));
    }

    public function test_close_register_successfully(): void
    {
        $register = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $response = $this->post(route('cash_registers.close', $register), [
            'closing_amount' => 500,
        ]);

        $response->assertRedirect(route('cash_registers.index'));
        $response->assertSessionHas('success');

        $register->refresh();
        $this->assertEquals('cerrada', $register->status);
        $this->assertEquals(500, $register->closing_amount);
    }

    public function test_close_register_creates_incident_on_mismatch(): void
    {
        $register = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $response = $this->post(route('cash_registers.close', $register), [
            'closing_amount' => 600,
            'notes' => 'Diferencia encontrada',
        ]);

        $response->assertRedirect(route('cash_registers.index'));
        $response->assertSessionHas('warning');

        $register->refresh();
        $this->assertEquals('cerrada', $register->status);
        $this->assertEquals(600, $register->closing_amount);

        $this->assertDatabaseHas('cash_register_incidents', [
            'cash_register_id' => $register->id,
            'expected_amount' => 500,
            'actual_amount' => 600,
            'difference' => -100,
            'status' => 'pendiente',
        ]);
    }

    public function test_close_register_within_tolerance_does_not_create_incident(): void
    {
        $register = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $response = $this->post(route('cash_registers.close', $register), [
            'closing_amount' => 500.50,
        ]);

        $response->assertRedirect(route('cash_registers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('cash_register_incidents', [
            'cash_register_id' => $register->id,
        ]);
    }

    public function test_withdraw_from_register(): void
    {
        $register = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $response = $this->post(route('cash_registers.withdraw', $register), [
            'amount' => 200,
            'reason' => 'Compra de insumos',
            'authorized_by' => 'Admin',
        ]);

        $response->assertRedirect(route('cash_registers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cash_register_movements', [
            'cash_register_id' => $register->id,
            'type' => 'retiro',
            'amount' => 200,
            'reason' => 'Compra de insumos',
            'authorized_by' => 'Admin',
        ]);
    }

    public function test_expected_cash_calculates_correctly(): void
    {
        $register = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        CashRegisterMovement::create([
            'cash_register_id' => $register->id,
            'type' => 'retiro',
            'amount' => 100,
            'reason' => 'Gasto',
        ]);

        $this->assertEquals(400, $register->getExpectedCash());
        $this->assertEquals(100, $register->getTotalWithdrawals());
    }

    public function test_index_shows_open_register(): void
    {
        $register = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $response = $this->get(route('cash_registers.index'));
        $response->assertOk();
        $response->assertSee('500');
    }

    public function test_index_shows_register_history(): void
    {
        $register = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now()->subDay(),
            'closed_at' => now(),
            'status' => 'cerrada',
            'closing_amount' => 500,
        ]);

        $response = $this->get(route('cash_registers.index'));
        $response->assertOk();
    }

    public function test_tenant_isolation(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherRegister = CashRegister::create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 999,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $response = $this->get(route('cash_registers.index'));
        $response->assertOk();
        $response->assertDontSee('999');
    }
}

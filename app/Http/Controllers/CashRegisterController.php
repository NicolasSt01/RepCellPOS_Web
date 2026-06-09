<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashRegisterMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CashRegisterController extends Controller
{
    public function index(Request $request): View
    {
        $registers = CashRegister::with('user')
            ->where('tenant_id', Auth::user()->tenant_id)
            ->latest()
            ->paginate(15);

        $openRegister = CashRegister::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'abierta')
            ->first();

        $movements = collect();
        if ($openRegister) {
            $movements = $openRegister->movements()->latest()->get();
        }

        $allMovements = CashRegisterMovement::with('cashRegister.user')
            ->whereHas('cashRegister', fn($q) => $q->where('tenant_id', Auth::user()->tenant_id))
            ->latest()
            ->paginate(20);

        return view('cash_registers.index', compact('registers', 'openRegister', 'movements', 'allMovements'));
    }

    public function open(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $existing = CashRegister::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'abierta')
            ->first();

        if ($existing) {
            return redirect()->route('cash_registers.index')->with('error', 'Ya hay una caja abierta.');
        }

        CashRegister::create([
            'tenant_id' => Auth::user()->tenant_id,
            'user_id' => Auth::id(),
            'opening_amount' => $validated['opening_amount'],
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        return redirect()->route('cash_registers.index')->with('success', 'Caja abierta exitosamente.');
    }

    public function close(Request $request, CashRegister $cashRegister): RedirectResponse
    {
        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cashRegister->update([
            'closing_amount' => $validated['closing_amount'],
            'closed_at' => now(),
            'status' => 'cerrada',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('cash_registers.index')->with('success', 'Caja cerrada exitosamente.');
    }

    public function withdraw(Request $request, CashRegister $cashRegister): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'authorized_by' => 'nullable|string|max:255',
        ]);

        CashRegisterMovement::create([
            'cash_register_id' => $cashRegister->id,
            'type' => 'retiro',
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'authorized_by' => $validated['authorized_by'] ?? null,
        ]);

        return redirect()->route('cash_registers.index')->with('success', 'Retiro registrado exitosamente.');
    }
}

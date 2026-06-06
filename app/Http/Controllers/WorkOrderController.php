<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Tenant;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = WorkOrder::with(['client', 'user'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('work_order_number', 'like', "%{$search}%")
                  ->orWhere('device_brand', 'like', "%{$search}%")
                  ->orWhere('device_model', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $workOrders = $query->paginate(15)->withQueryString();

        return view('work_orders.index', compact('workOrders', 'status', 'priority', 'search'));
    }

    public function create(): View
    {
        $clients = Client::orderBy('name')->get();
        return view('work_orders.create', compact('clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'device_brand' => 'required|string|max:255',
            'device_model' => 'required|string|max:255',
            'device_serial' => 'nullable|string|max:255',
            'device_imei' => 'nullable|string|max:255',
            'unlock_pattern' => 'nullable|string|max:255',
            'unlock_pin' => 'nullable|string|max:255',
            'problem_description' => 'required|string',
        ]);

        $tenant = Auth::user()->tenant;
        $workOrderNumber = WorkOrder::generateWorkOrderNumber($tenant);

        $workOrder = WorkOrder::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'work_order_number' => $workOrderNumber,
            'status' => 'recibida',
            'priority' => 'media',
        ]));

        $workOrder->addTimelineEvent(
            'recibida',
            Auth::user()->name,
            'Equipo recibido y registrado'
        );

        $workOrder->update(['status' => 'en_espera']);
        $workOrder->addTimelineEvent(
            'en_espera',
            'Sistema',
            'Orden pendiente de ser tomada por un técnico'
        );

        return redirect()->route('work_orders.show', $workOrder)
            ->with('success', "Orden de trabajo {$workOrderNumber} creada exitosamente.");
    }

    public function show(WorkOrder $workOrder): View
    {
        $workOrder->load(['client', 'user', 'quote.quoteItems']);
        return view('work_orders.show', compact('workOrder'));
    }

    public function edit(WorkOrder $workOrder): View
    {
        if (!in_array($workOrder->status, ['recibida', 'en_espera'])) {
            return redirect()->route('work_orders.show', $workOrder)
                ->with('error', 'Solo se pueden editar órdenes en estado recibida o en espera.');
        }

        $clients = Client::orderBy('name')->get();
        return view('work_orders.edit', compact('workOrder', 'clients'));
    }

    public function update(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        if (!in_array($workOrder->status, ['recibida', 'en_espera'])) {
            return redirect()->route('work_orders.show', $workOrder)
                ->with('error', 'Solo se pueden editar órdenes en estado recibida o en espera.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'device_brand' => 'required|string|max:255',
            'device_model' => 'required|string|max:255',
            'device_serial' => 'nullable|string|max:255',
            'device_imei' => 'nullable|string|max:255',
            'unlock_pattern' => 'nullable|string|max:255',
            'unlock_pin' => 'nullable|string|max:255',
            'problem_description' => 'required|string',
        ]);

        $workOrder->update($validated);

        return redirect()->route('work_orders.show', $workOrder)
            ->with('success', 'Orden de trabajo actualizada exitosamente.');
    }

    public function changeStatus(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'comment' => 'nullable|string|max:1000',
        ]);

        if (!$workOrder->canTransitionTo($validated['status'])) {
            return redirect()->route('work_orders.show', $workOrder)
                ->with('error', 'Transición de estado no válida.');
        }

        $workOrder->update(['status' => $validated['status']]);
        $workOrder->addTimelineEvent(
            $validated['status'],
            Auth::user()->name,
            $validated['comment'] ?? null
        );

        return redirect()->route('work_orders.show', $workOrder)
            ->with('success', 'Estado actualizado exitosamente.');
    }

    public function setPriority(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'priority' => 'required|in:baja,media,alta',
        ]);

        $workOrder->update(['priority' => $validated['priority']]);

        return redirect()->route('work_orders.show', $workOrder)
            ->with('success', 'Prioridad actualizada exitosamente.');
    }

    public function addNote(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $workOrder->addTimelineEvent(
            $workOrder->status,
            Auth::user()->name,
            $validated['comment']
        );

        return redirect()->route('work_orders.show', $workOrder)
            ->with('success', 'Anotación agregada exitosamente.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Tenant;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = WorkOrder::with(['client', 'user', 'assignedTechnician'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($assignedTo = $request->input('assigned_to')) {
            if ($assignedTo === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $assignedTo);
            }
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

        $technicians = User::whereHas('roles', fn($q) => $q->where('name', 'Tecnico'))->orderBy('name')->get();

        return view('work_orders.index', compact('workOrders', 'status', 'priority', 'search', 'technicians'));
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
        $workOrder->load(['client', 'user', 'assignedTechnician', 'quote.quoteItems']);
        $technicians = User::whereHas('roles', fn($q) => $q->where('name', 'Tecnico'))->orderBy('name')->get();
        return view('work_orders.show', compact('workOrder', 'technicians'));
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

    public function reports(Request $request): View
    {
        $query = WorkOrder::with(['client', 'user', 'assignedTechnician']);

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($assignedTo = $request->input('assigned_to')) {
            if ($assignedTo === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $assignedTo);
            }
        }

        $workOrders = $query->latest()->paginate(25)->withQueryString();

        $technicians = User::whereHas('roles', fn($q) => $q->where('name', 'Tecnico'))->orderBy('name')->get();

        $summary = WorkOrder::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status NOT IN ('terminada','cancelada') THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN status = 'terminada' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = 'cancelada' THEN 1 ELSE 0 END) as cancelled_count,
            SUM(CASE WHEN status IN ('en_espera','recibida') THEN 1 ELSE 0 END) as pending_count
        ")->first();

        $byStatus = WorkOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $byTechnician = WorkOrder::selectRaw('assigned_to, count(*) as total')
            ->whereNotNull('assigned_to')
            ->whereNotIn('status', ['terminada', 'cancelada'])
            ->groupBy('assigned_to')
            ->with('assignedTechnician')
            ->orderByDesc('total')
            ->get();

        return view('work_orders.reports', compact('workOrders', 'technicians', 'summary', 'byStatus', 'byTechnician'));
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

    public function assignTechnician(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $technician = User::find($validated['assigned_to']);

        $workOrder->update(['assigned_to' => $technician->id]);
        $workOrder->addTimelineEvent(
            $workOrder->status,
            Auth::user()->name,
            "Técnico asignado: {$technician->name}"
        );

        return redirect()->route('work_orders.show', $workOrder)
            ->with('success', "Técnico {$technician->name} asignado a la orden.");
    }

    public function unassignTechnician(WorkOrder $workOrder): RedirectResponse
    {
        $technician = $workOrder->assignedTechnician;
        $workOrder->update(['assigned_to' => null]);

        if ($technician) {
            $workOrder->addTimelineEvent(
                $workOrder->status,
                Auth::user()->name,
                "Técnico desasignado: {$technician->name}"
            );
        }

        return redirect()->route('work_orders.show', $workOrder)
            ->with('success', 'Técnico desasignado de la orden.');
    }
}

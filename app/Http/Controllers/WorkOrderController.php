<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\R2StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

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

        $technicians = $this->getAssignableUsers();

        return view('work_orders.index', compact('workOrders', 'status', 'priority', 'search', 'technicians'));
    }

    public function create(): View
    {
        $clients = Client::orderBy('name')->get();
        return view('work_orders.create', compact('clients'));
    }

    /**
     * AJAX endpoint for searching clients by name or phone.
     */
    public function searchClients(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $clients = Client::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%");
        })->orderBy('name')->limit(10)->get(['id', 'name', 'phone', 'email']);

        return response()->json($clients);
    }

    /**
     * AJAX endpoint for creating a client inline from the multi-step form.
     */
    public function storeClient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'notification_preference' => 'required|in:whatsapp,email,call',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $client = Client::create($validated);

        return response()->json([
            'id' => $client->id,
            'name' => $client->name,
            'phone' => $client->phone,
            'email' => $client->email,
        ]);
    }

    public function store(Request $request, R2StorageService $r2): RedirectResponse
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
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,webp|max:5120',
            'captured_images' => 'nullable|array|max:5',
            'captured_images.*' => 'nullable|string',
        ]);

        $tenant = Auth::user()->tenant;
        $workOrderNumber = WorkOrder::generateWorkOrderNumber($tenant);

        // Upload images to R2
        $imagePaths = [];

        // Handle file uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $r2->upload($image, 'work_orders');
            }
        }

        // Handle base64 captured images (from camera)
        if ($request->has('captured_images')) {
            foreach ($request->input('captured_images') as $base64Image) {
                if (!empty($base64Image)) {
                    try {
                        $imagePaths[] = $r2->uploadFromBase64($base64Image, 'work_orders');
                    } catch (\Exception $e) {
                        // Skip invalid images silently
                    }
                }
            }
        }

        $orderData = collect($validated)->only([
            'client_id', 'device_brand', 'device_model', 'device_serial',
            'device_imei', 'unlock_pattern', 'unlock_pin', 'problem_description',
        ])->toArray();

        $workOrder = WorkOrder::create(array_merge($orderData, [
            'user_id' => Auth::id(),
            'work_order_number' => $workOrderNumber,
            'status' => 'recibida',
            'priority' => 'media',
            'images' => !empty($imagePaths) ? $imagePaths : null,
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
        $technicians = $this->getAssignableUsers();
        $cashRegister = \App\Models\CashRegister::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'abierta')
            ->first();
        return view('work_orders.show', compact('workOrder', 'technicians', 'cashRegister'));
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

        $technicians = $this->getAssignableUsers();

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

    private function getAssignableUsers()
    {
        $workOrderPerms = Permission::where('name', 'like', 'work_orders.%')->pluck('id');

        return User::where('tenant_id', auth()->user()->tenant_id)
            ->where(function ($q) use ($workOrderPerms) {
                $q->whereHas('roles', fn($q) => $q->whereIn('name', ['Tecnico', 'Admin Tenant']))
                  ->orWhereHas('roles.permissions', fn($q) => $q->whereIn('permissions.id', $workOrderPerms));
            })
            ->orderBy('name')
            ->get();
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

    public function addImages(Request $request, WorkOrder $workOrder, R2StorageService $r2): RedirectResponse
    {
        $request->validate([
            'images' => 'required|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,webp|max:5120',
        ]);

        $newPaths = [];
        foreach ($request->file('images') as $image) {
            $newPaths[] = $r2->upload($image, 'work_orders');
        }

        $existingImages = $workOrder->images ?? [];
        $workOrder->update([
            'images' => array_merge($existingImages, $newPaths),
        ]);

        $workOrder->addTimelineEvent(
            $workOrder->status,
            Auth::user()->name,
            'Se agregaron ' . count($newPaths) . ' foto(s) al expediente del equipo'
        );

        return redirect()->route('work_orders.show', $workOrder)
            ->with('success', count($newPaths) . ' foto(s) agregada(s) al expediente del equipo.');
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

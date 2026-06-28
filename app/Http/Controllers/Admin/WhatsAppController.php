<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Services\EvolutionApiService;
use App\Services\NotificationService;
use App\Services\TenantMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WhatsAppController extends Controller
{
    protected EvolutionApiService $evolution;

    public function __construct(EvolutionApiService $evolution)
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login');
            }

            $tenant = $user->tenant;
            if (!$tenant || !$tenant->hasFeature('notifications_whatsapp')) {
                return redirect()->route('settings.index')
                    ->with('error', 'WhatsApp no está disponible en tu plan.');
            }

            return $next($request);
        });

        $this->evolution = $evolution;
    }

    public function index()
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $evolutionConfig = $config['evolution_api'] ?? [];

        $instance = $evolutionConfig['instance'] ?? null;
        if ($instance && $this->evolution->isConfigured()) {
            $stateResult = $this->evolution->getConnectionState($instance);
            $instanceData = $stateResult['instance'] ?? $stateResult;
            $realState = $instanceData['state'] ?? $instanceData['status'] ?? '';

            if ($realState === 'open' && !($evolutionConfig['connected'] ?? false)) {
                $config['evolution_api']['connected'] = true;
                $config['evolution_api']['connected_at'] = now()->toDateTimeString();
                $tenant->update(['configuracion' => $config]);
                $evolutionConfig = $config['evolution_api'];
            } elseif ($realState !== 'open' && ($evolutionConfig['connected'] ?? false)) {
                $config['evolution_api']['connected'] = false;
                $tenant->update(['configuracion' => $config]);
                $evolutionConfig = $config['evolution_api'];
            }
        }

        $estado = $this->determinarEstado($evolutionConfig);

        return view('admin.config.whatsapp', compact('evolutionConfig', 'estado'));
    }

    public function conectar(Request $request): JsonResponse
    {
        $tenant = Auth::user()->tenant;

        if (!$this->evolution->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Evolution API no está configurada.',
            ], 500);
        }

        $instanceName = 'tenant_' . $tenant->id;

        $healthResult = $this->evolution->ping();
        $healthStatus = $healthResult['_status'] ?? 0;

        if ($healthStatus !== 200) {
            return response()->json([
                'success' => false,
                'message' => "Sin conexión a Evolution API (HTTP {$healthStatus}).",
            ], 500);
        }

        $stateCheck = $this->evolution->getConnectionState($instanceName);
        $instanceData = $stateCheck['instance'] ?? $stateCheck;
        if ($stateCheck['_status'] === 200 && ($instanceData['state'] ?? '') === 'open') {
            $this->guardarEstado($tenant, $instanceName, true);
            return response()->json([
                'success' => true,
                'paired' => true,
                'message' => 'WhatsApp ya está conectado.',
            ]);
        }

        $createResult = $this->evolution->createInstance($instanceName);
        $createBody = $createResult;
        $createStatus = $createResult['_status'] ?? 0;

        $instanceData = $createBody['instance'] ?? $createBody;
        $paired = ($instanceData['state'] ?? '') === 'open';
        $apiError = $createBody['error'] ?? $createBody['message'] ?? null;

        $instanceExists = in_array($createStatus, [400, 403]) && (
            stripos($apiError ?? '', 'exist') !== false ||
            stripos($apiError ?? '', 'already') !== false ||
            stripos(json_encode($createBody), 'exist') !== false ||
            stripos(json_encode($createBody), 'already') !== false
        );

        $qr = $this->extraerQr($createBody);

        if ($instanceExists && !$qr) {
            Log::info('[WhatsApp] Instancia ya existe, reconectando', ['instance' => $instanceName]);

            $connectResult = $this->evolution->connectInstance($instanceName);
            $qr = $this->extraerQr($connectResult);

            if (!$qr) {
                usleep(500000);
                $qrResult = $this->evolution->getQrCode($instanceName);
                $qr = $this->extraerQr($qrResult);
            }

            if (!$qr) {
                Log::info('[WhatsApp] Reconexión falló, borrando y recreando', ['instance' => $instanceName]);
                $this->evolution->deleteInstance($instanceName);
                usleep(1000000);
                $createResult = $this->evolution->createInstance($instanceName);
                $createBody = $createResult;
                $createStatus = $createResult['_status'] ?? 0;
                $apiError = $createBody['error'] ?? $createBody['message'] ?? null;
                $qr = $this->extraerQr($createBody);

                if (!$qr && $createStatus >= 200 && $createStatus < 300) {
                    usleep(500000);
                    $qrResult = $this->evolution->getQrCode($instanceName);
                    $qr = $this->extraerQr($qrResult);
                    if (!$qr) {
                        $apiError = $qrResult['error'] ?? $apiError;
                    }
                }
            }

            if ($qr) {
                $apiError = null;
            }
        }

        if (!$qr && !$apiError && $createStatus >= 200 && $createStatus < 300) {
            usleep(500000);
            $connectResult = $this->evolution->connectInstance($instanceName);
            $qr = $this->extraerQr($connectResult);

            if (!$qr) {
                $qrResult = $this->evolution->getQrCode($instanceName);
                $qr = $this->extraerQr($qrResult);
                if (!$qr) {
                    $apiError = $qrResult['error'] ?? $connectResult['error'] ?? $apiError;
                }
            }
        }

        $this->guardarEstado($tenant, $instanceName, $paired);

        return response()->json([
            'success' => true,
            'instance' => $instanceName,
            'qr' => $qr,
            'paired' => $paired,
            'message' => $paired
                ? 'WhatsApp ya está conectado.'
                : ($qr ? 'Escanea el código QR.' : 'No se pudo obtener el QR.'),
        ]);
    }

    public function estado(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $evolutionConfig = $config['evolution_api'] ?? [];
        $instance = $evolutionConfig['instance'] ?? null;

        if (!$instance || !$this->evolution->isConfigured()) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'No hay instancia configurada.',
            ]);
        }

        $result = $this->evolution->getConnectionState($instance);
        $instanceData = $result['instance'] ?? $result;
        $realState = $instanceData['state'] ?? $instanceData['status'] ?? '';
        $connected = $realState === 'open';

        if ($connected !== ($evolutionConfig['connected'] ?? false)) {
            $config['evolution_api']['connected'] = $connected;
            if ($connected) {
                $config['evolution_api']['connected_at'] = now()->toDateTimeString();
            }
            $tenant->update(['configuracion' => $config]);
        }

        return response()->json([
            'success' => true,
            'connected' => $connected,
            'state' => $realState ?: 'unknown',
            'instance' => $instance,
        ]);
    }

    public function desconectar(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $instance = $config['evolution_api']['instance'] ?? null;

        if (!$instance) {
            return response()->json([
                'success' => false,
                'message' => 'No hay instancia para desconectar.',
            ], 400);
        }

        $this->evolution->logout($instance);

        $config['evolution_api']['connected'] = false;
        $config['evolution_api']['connected_at'] = null;
        $tenant->update(['configuracion' => $config]);

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp desconectado.',
        ]);
    }

    public function contactos(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $instance = $config['evolution_api']['instance'] ?? null;

        if (!$instance || !$this->evolution->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'No hay instancia configurada.'], 400);
        }

        $result = $this->evolution->fetchContacts($instance);

        $contactos = [];
        foreach ($result as $key => $item) {
            if ($key === '_status' || $key === '_error' || !is_array($item)) continue;
            $id = $item['remoteJid'] ?? $item['id'] ?? '';
            if (str_ends_with($id, '@s.whatsapp.net')) {
                $contactos[] = [
                    'id' => $id,
                    'nombre' => $item['pushName'] ?? ($item['name'] ?? 'Sin nombre'),
                    'telefono' => explode('@', $id)[0],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'contactos' => $contactos,
            'total' => count($contactos),
        ]);
    }

    public function grupos(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $instance = $config['evolution_api']['instance'] ?? null;

        if (!$instance || !$this->evolution->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'No hay instancia configurada.'], 400);
        }

        $result = $this->evolution->fetchContacts($instance);
        $grupos = [];
        foreach ($result as $key => $item) {
            if ($key === '_status' || $key === '_error' || !is_array($item)) continue;
            $id = $item['remoteJid'] ?? $item['id'] ?? '';
            if (str_ends_with($id, '@g.us')) {
                $grupos[] = [
                    'id' => $id,
                    'nombre' => $item['pushName'] ?? ($item['name'] ?? 'Grupo sin nombre'),
                    'participants' => [],
                    'participant_count' => 0,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'grupos' => $grupos,
        ]);
    }


    public function pendientes(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $whatsapp = $tenant->getPendingNotifications('whatsapp');

        $infoWhatsapp = [
            'limite' => $tenant->getLimiteFuncionalidad('whatsapp_mes'),
            'uso' => $tenant->getWhatsappUsadosMes(),
        ];

        return response()->json([
            'success' => true,
            'pendientes_whatsapp' => array_map(fn($p) => $this->formatearPendiente($p), $whatsapp),
            'total_whatsapp' => count($whatsapp),
            'info_whatsapp' => $infoWhatsapp,
        ]);
    }

    public function reenviarPendiente(Request $request): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $id = $request->input('id');

        $pendientes = $tenant->getPendingNotifications();
        $pendiente = collect($pendientes)->firstWhere('id', $id);

        if (!$pendiente) {
            return response()->json(['success' => false, 'message' => 'Pendiente no encontrado.'], 404);
        }

        $data = $pendiente['data'];

        try {
            if (!$tenant->canSendWhatsapp()) {
                $limite = $tenant->getLimiteFuncionalidad('whatsapp_mes');
                $uso = $tenant->getWhatsappUsadosMes();
                return response()->json([
                    'success' => false,
                    'limit_exceeded' => true,
                    'message' => "Límite de {$limite} mensajes alcanzado ({$uso}/{$limite}).",
                ], 403);
            }

            $workOrder = WorkOrder::find($data['work_order_id'] ?? null);
            if ($workOrder) {
                app(NotificationService::class)->send($workOrder, $data['event'] ?? 'status_changed');
            }

            $tenant->removePendingNotification($id);

            return response()->json(['success' => true, 'message' => 'Notificación reenviada.']);
        } catch (\Exception $e) {
            Log::error("[WhatsApp] Error reenviando pendiente {$id}", ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function descartarPendiente(Request $request): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $tenant->removePendingNotification($request->input('id'));
        return response()->json(['success' => true, 'message' => 'Notificación descartada.']);
    }

    public function descartarTodasPendientes(Request $request): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $tenant->clearPendingNotifications('whatsapp');
        return response()->json(['success' => true, 'message' => 'Pendientes descartadas.']);
    }

    public function debugInstancias(): JsonResponse
    {
        $listResult = $this->evolution->listInstances();
        $healthResult = $this->evolution->ping();

        return response()->json([
            'health' => ['status' => $healthResult['_status'] ?? 0, 'body' => $healthResult],
            'instances' => ['status' => $listResult['_status'] ?? 0, 'body' => $listResult],
            'config' => [
                'base_url' => env('EVOLUTION_API_BASE_URL'),
                'api_key_set' => !empty(env('EVOLUTION_API_KEY')),
            ],
        ]);
    }

    public function debugContactos(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $instance = $config['evolution_api']['instance'] ?? null;

        if (!$instance) {
            return response()->json(['error' => 'No instance']);
        }

        return response()->json([
            'contacts' => $this->evolution->fetchContacts($instance),
            'groups' => $this->evolution->fetchGroups($instance, false),
        ]);
    }

    protected function guardarEstado($tenant, string $instance, bool $connected): void
    {
        $config = $tenant->configuracion ?? [];
        $config['evolution_api'] = [
            'instance' => $instance,
            'connected' => $connected,
            'connected_at' => $connected ? now()->toDateTimeString() : ($config['evolution_api']['connected_at'] ?? null),
        ];
        $tenant->update(['configuracion' => $config]);
    }

    protected function determinarEstado(array $evolutionConfig): int
    {
        $instance = $evolutionConfig['instance'] ?? null;
        $connected = $evolutionConfig['connected'] ?? false;

        if (!$instance) return 0;
        if ($connected) return 2;
        return 1;
    }

    protected function extraerQr(array $body): ?string
    {
        $qrcode = $body['qrcode'] ?? $body['qr'] ?? null;
        if (is_array($qrcode)) {
            return $qrcode['base64'] ?? $qrcode['qr'] ?? $qrcode['image'] ?? null;
        }
        if (is_string($qrcode) && !empty($qrcode)) {
            return $qrcode;
        }
        if (isset($body['instance'])) {
            return $this->extraerQr($body['instance']);
        }
        return null;
    }

    protected function formatearPendiente(array $p): array
    {
        $data = $p['data'];
        return [
            'id' => $p['id'],
            'type' => $p['type'],
            'created_at' => $p['created_at'],
            'cliente' => $data['client_name'] ?? 'N/A',
            'evento' => $data['event'] ?? 'N/A',
            'destinatarios' => 1,
        ];
    }
}

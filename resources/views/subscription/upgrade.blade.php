@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Elige tu plan</h1>
        <p class="mt-2 text-gray-500 dark:text-gray-400">
            @if($tenant->subscription_status === 'trial')
                Tu prueba gratuita termina el {{ $tenant->trial_ends_at->format('d/m/Y') }}.
            @elseif($tenant->subscription_status === 'expired')
                Tu período de prueba ha terminado. Elige un plan para continuar.
            @else
                Gestiona tu suscripción.
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-700 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-700 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    @if($pendingPayment)
        <div class="mb-6 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-6">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-2">Pago pendiente</h3>
            <p class="text-sm text-blue-700 dark:text-blue-300 mb-4">
                Seleccionaste el plan <strong>{{ $pendingPayment->plan->name ?? $pendingPayment->plan_type }}</strong>
                por <strong>${{ number_format($pendingPayment->amount, 2) }} MXN</strong>.
                Realiza tu pago y sube el comprobante.
            </p>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-4 border border-blue-200 dark:border-blue-700">
                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Datos para transferencia</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Banco: NexaCore</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">CLABE: 000 000 000 000 000 000</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Titular: NexaCore Software</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Concepto: {{ $tenant->slug }} - {{ $pendingPayment->plan->name ?? $pendingPayment->plan_type }}</p>
            </div>
            @if(!$pendingPayment->payment_proof)
                <form method="POST" action="{{ route('subscription.payment-proof') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="hidden" name="subscription_id" value="{{ $pendingPayment->id }}">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Comprobante de pago</label>
                    <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">
                    @error('payment_proof')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        Subir comprobante
                    </button>
                </form>
            @else
                <p class="text-sm text-green-600 dark:text-green-400">✅ Comprobante subido. Espera a que el administrador lo confirme.</p>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            @php
                $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                $isPending = $pendingPayment && $pendingPayment->plan_id === $plan->id;
            @endphp
            <div class="relative rounded-2xl border-2 {{ $plan->is_highlight ? 'border-indigo-500 shadow-xl scale-105' : 'border-gray-200 dark:border-gray-700' }} bg-white dark:bg-gray-800 p-6 transition-all hover:shadow-lg">
                @if($plan->is_highlight)
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 inline-flex items-center rounded-full bg-indigo-600 px-3 py-1 text-xs font-semibold text-white shadow-sm">Recomendado</span>
                @endif
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $plan->name }}</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                <p class="mt-4">
                    <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($plan->price, 2) }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">/mes</span>
                </p>

                @if($isCurrent && $tenant->subscription_status === 'active')
                    <span class="mt-4 inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-3 py-1 text-xs font-semibold text-green-700 dark:text-green-400">Plan actual</span>
                @elseif($isPending)
                    <span class="mt-4 inline-flex items-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-3 py-1 text-xs font-semibold text-yellow-700 dark:text-yellow-400">Pendiente de pago</span>
                @elseif($tenant->subscription_status !== 'trial' || ($tenant->trial_ends_at && now()->gt($tenant->trial_ends_at)))
                    <form method="POST" action="{{ route('subscription.select') }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <button type="submit" class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                            Seleccionar plan
                        </button>
                    </form>
                @endif

                <ul class="mt-6 space-y-3">
                    @foreach($plan->features as $feature => $enabled)
                        <li class="flex items-center text-sm {{ $enabled ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500' }}">
                            @if($enabled)
                                <svg class="mr-2 h-4 w-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            @else
                                <svg class="mr-2 h-4 w-4 text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                            @switch($feature)
                                @case('work_orders') Órdenes de Trabajo @break
                                @case('quotes') Cotizaciones @break
                                @case('pos') Punto de Venta (POS) @break
                                @case('notifications_email') Notificaciones Email @break
                                @case('notifications_whatsapp') Notificaciones WhatsApp @break
                                @case('notifications_low_stock') Alertas de Stock Bajo @break
                                @case('reports_advanced') Reportes Avanzados @break
                                @default {{ $feature }}
                            @endswitch
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Límites</p>
                    <ul class="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        @foreach($plan->limits as $limit => $value)
                            <li>
                                @switch($limit)
                                    @case('max_users') Usuarios: {{ $value === -1 ? 'Ilimitados' : $value }} @break
                                    @case('max_clients') Clientes: {{ $value === -1 ? 'Ilimitados' : $value }} @break
                                    @case('max_monthly_work_orders') OT/mes: {{ $value === -1 ? 'Ilimitadas' : $value }} @break
                                    @case('storage_mb') Almacenamiento: {{ $value }} MB @break
                                    @default {{ $limit }}: {{ $value }}
                                @endswitch
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

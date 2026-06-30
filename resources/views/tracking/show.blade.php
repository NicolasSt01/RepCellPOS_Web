<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seguimiento de Orden — {{ $workOrder->work_order_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full">
    <div class="min-h-full py-12 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl">
            <div class="text-center mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
                    <img src="{{ asset('logo.png') }}" alt="RepCellPOS" class="w-8 h-8 rounded-lg object-cover">
                    <span class="text-3xl font-bold text-indigo-600">RepCell<span class="text-gray-900">POS</span></span>
                </a>
                <p class="mt-2 text-lg text-gray-600">Seguimiento de Orden de Trabajo</p>
            </div>

            @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 rounded-lg border border-green-200">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 rounded-lg border border-red-200">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
            @endif
            @if(session('info'))
            <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
            </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="p-6 bg-indigo-50 border-b border-indigo-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $workOrder->work_order_number }}</h2>
                            <p class="text-sm text-gray-600">{{ $workOrder->tenant?->name ?? 'Taller' }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-medium
                            @if($workOrder->status === 'terminada') bg-green-100 text-green-800
                            @elseif($workOrder->status === 'cancelada') bg-red-100 text-red-800
                            @else bg-indigo-100 text-indigo-800
                            @endif">
                            {{ ucwords(str_replace('_', ' ', $workOrder->status)) }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Equipo</p>
                            <p class="text-sm font-medium text-gray-900">{{ $workOrder->device_brand }} {{ $workOrder->device_model }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha de ingreso</p>
                            <p class="text-sm font-medium text-gray-900">{{ $workOrder->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Progreso de la Reparación</h3>

                    @php
                        $steps = ['recibida', 'en_espera', 'en_revision', 'diagnosticada', 'cotizacion_enviada', 'cotizacion_aprobada', 'en_reparacion', 'reparada', 'terminada'];
                        $currentStep = array_search($workOrder->status, $steps);
                        if ($workOrder->status === 'cancelada') $currentStep = -1;
                    @endphp

                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        <div class="space-y-6">
                            @foreach($steps as $index => $step)
                            @php
                                $isCompleted = $currentStep >= $index;
                                $isCurrent = $currentStep === $index;
                                $stepEvents = collect($workOrder->timeline ?? [])->where('estado', $step);
                            @endphp
                            <div class="relative flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center z-10
                                    @if($isCompleted) bg-green-500 text-white
                                    @elseif($isCurrent) bg-indigo-500 text-white ring-4 ring-indigo-100
                                    @else bg-gray-200 text-gray-400
                                    @endif">
                                    @if($isCompleted)
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                    @else
                                        <span class="text-xs font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium
                                        @if($isCompleted) text-green-700
                                        @elseif($isCurrent) text-indigo-700
                                        @else text-gray-400
                                        @endif">
                                        {{ ucwords(str_replace('_', ' ', $step)) }}
                                    </p>
                                    @foreach($stepEvents as $event)
                                    <div class="mt-1">
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($event['fecha'])->format('d/m/Y H:i') }} — {{ $event['usuario'] }}</p>
                                        @if($event['comentario'])
                                        <p class="text-xs text-gray-600 mt-0.5">{{ $event['comentario'] }}</p>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @if($workOrder->status === 'cancelada')
                    <div class="mt-6 p-4 bg-red-50 rounded-lg border border-red-200">
                        <p class="text-sm font-medium text-red-800">Orden Cancelada</p>
                        @php $cancelEvent = collect($workOrder->timeline ?? [])->where('estado', 'cancelada')->last(); @endphp
                        @if($cancelEvent && $cancelEvent['comentario'])
                        <p class="text-sm text-red-600 mt-1">{{ $cancelEvent['comentario'] }}</p>
                        @endif
                    </div>
                    @endif

                    @if($workOrder->quote && in_array($workOrder->quote->status, ['enviada', 'aprobada', 'rechazada']))
                    @php $quote = $workOrder->quote; @endphp
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Cotización</h3>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 pr-2 font-medium text-gray-500">Descripción</th>
                                        <th class="text-center py-2 px-2 font-medium text-gray-500">Tipo</th>
                                        <th class="text-center py-2 px-2 font-medium text-gray-500">Cant.</th>
                                        <th class="text-right py-2 px-2 font-medium text-gray-500">P. Unit.</th>
                                        <th class="text-right py-2 pl-2 font-medium text-gray-500">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($quote->quoteItems as $item)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2 pr-2 text-gray-900">{{ $item->description }}</td>
                                        <td class="py-2 px-2 text-center">
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium
                                                @if($item->type === 'producto') bg-blue-100 text-blue-700
                                                @else bg-gray-100 text-gray-700 @endif">
                                                {{ $item->type === 'producto' ? 'Producto' : 'Servicio' }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-2 text-center text-gray-900">{{ $item->quantity }}</td>
                                        <td class="py-2 px-2 text-right text-gray-900">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="py-2 pl-2 text-right text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-gray-400">Sin items</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 space-y-1 text-sm text-right">
                            <p class="text-gray-500">Subtotal: <span class="font-medium text-gray-900">${{ number_format($quote->subtotal, 2) }}</span></p>
                            @if($quote->tax_total > 0)
                            <p class="text-gray-500">Impuestos: <span class="font-medium text-gray-900">${{ number_format($quote->tax_total, 2) }}</span></p>
                            @endif
                            <p class="text-lg font-bold text-gray-900">Total: ${{ number_format($quote->total, 2) }}</p>
                        </div>

                        @if($quote->status === 'enviada')
                        <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-end">
                            <form method="POST" action="{{ route('tracking.reject-quote', $workOrder->tracking_token) }}"
                                  x-data="{ open: false, reason: '' }"
                                  @submit.prevent="if(open) $el.submit(); else open = true">
                                @csrf
                                <div x-show="open" x-cloak class="mb-3 w-full sm:w-80">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo del rechazo (opcional)</label>
                                    <textarea x-model="reason" name="reason" rows="2"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-red-500 sm:text-sm sm:leading-6"
                                        placeholder="Ej: Presupuesto muy elevado"></textarea>
                                </div>
                                <div class="flex gap-3 justify-end">
                                    <button type="submit"
                                        class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                                        <span x-show="!open">Rechazar Cotización</span>
                                        <span x-show="open">Confirmar Rechazo</span>
                                    </button>
                                    <button type="button" @click="open = false" x-show="open"
                                        class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('tracking.approve-quote', $workOrder->tracking_token) }}">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('¿Estás seguro de aprobar esta cotización? Al hacerlo, se reservará el stock necesario.')"
                                    class="inline-flex items-center rounded-md bg-green-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                                    Aprobar Cotización
                                </button>
                            </form>
                        </div>
                        @elseif($quote->status === 'aprobada')
                        <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                            <p class="text-sm font-medium text-green-800">✓ Cotización aprobada</p>
                            <p class="text-xs text-green-600 mt-1">El taller ya tiene tu aprobación y está procesando la reparación.</p>
                        </div>
                        @elseif($quote->status === 'rechazada')
                        <div class="mt-4 p-3 bg-red-50 rounded-lg border border-red-200">
                            <p class="text-sm font-medium text-red-800">✗ Cotización rechazada</p>
                            @if($quote->cancellation_reason)
                            <p class="text-xs text-red-600 mt-1">Motivo: {{ $quote->cancellation_reason }}</p>
                            @endif
                            <p class="text-xs text-red-500 mt-1">El taller revisará tu decisión y se pondrá en contacto contigo.</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-gray-400">
                Si tienes alguna duda, contacta al taller directamente.
            </p>
        </div>
    </div>
</body>
</html>

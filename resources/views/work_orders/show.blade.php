@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Orden {{ $workOrder->work_order_number }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Creada el {{ $workOrder->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            @if(in_array($workOrder->status, ['recibida', 'en_espera']))
            <a href="{{ route('work_orders.edit', $workOrder) }}"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                Editar
            </a>
            @endif
            <a href="{{ route('work_orders.index') }}"
                class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Información del Cliente</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $workOrder->client->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $workOrder->client->phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $workOrder->client->email ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notificación</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                @php $pref = $workOrder->client->notification_preference; @endphp
                                @if($pref === 'email')
                                    📧 Correo
                                @elseif($pref === 'whatsapp')
                                    💬 WhatsApp
                                @elseif($pref === 'call')
                                    📞 Llamada telefónica
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Datos del Dispositivo</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Marca</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $workOrder->device_brand }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Modelo</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $workOrder->device_model }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Serie</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $workOrder->device_serial ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">IMEI</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $workOrder->device_imei ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Patrón de Desbloqueo</dt>
                            <dd class="mt-1">
                                @if($workOrder->unlock_pattern)
                                <canvas class="pattern-visualizer" data-pattern="{{ $workOrder->unlock_pattern }}" width="120" height="120" style="max-width:120px;height:auto;"></canvas>
                                <div class="mt-2 flex items-center gap-1 text-xs font-mono text-gray-600 dark:text-gray-400">
                                    @foreach(str_split($workOrder->unlock_pattern) as $i => $dot)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold">{{ $dot }}</span>
                                    @if($i < strlen($workOrder->unlock_pattern) - 1)
                                    <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                                    @endif
                                    @endforeach
                                </div>
                                <div class="mt-1.5 grid grid-cols-3 w-24 gap-0 border border-gray-200 dark:border-gray-600 rounded overflow-hidden">
                                    @for($i = 1; $i <= 9; $i++)
                                    <div class="flex items-center justify-center h-6 text-[10px] font-mono font-bold {{ in_array((string)$i, str_split($workOrder->unlock_pattern)) ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500' }}">{{ $i }}</div>
                                    @endfor
                                </div>
                                @else
                                <span class="text-sm text-gray-900 dark:text-gray-100">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">PIN</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $workOrder->unlock_pin ?? '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Problema Reportado</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $workOrder->problem_description }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @php $r2 = app(\App\Services\R2StorageService::class); @endphp
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Fotos del Equipo</h2>

                    @if($workOrder->images && count($workOrder->images) > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                        @foreach($workOrder->images as $path)
                        <a href="{{ $r2->getUrl($path) }}" target="_blank" rel="noopener noreferrer"
                            class="block aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 ring-1 ring-gray-200 dark:ring-gray-600 hover:ring-indigo-500 transition-all">
                            <img src="{{ $r2->getUrl($path) }}"
                                alt="Foto del equipo"
                                class="w-full h-full object-cover"
                                loading="lazy">
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Aún no se han agregado fotos de este equipo.</p>
                    @endif

                    <form method="POST" action="{{ route('work_orders.images.store', $workOrder) }}"
                        enctype="multipart/form-data" class="border-t border-gray-200 dark:border-gray-700 pt-4"
                        x-data="imageUploader()">
                        @csrf
                        <div class="space-y-3">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Agregar más fotos</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Toma fotos durante la reparación para mostrarle al cliente el estado de su equipo. 
                                Máximo 5 fotos por carga.
                            </p>

                            <div @click="$refs.fileInput.click()"
                                @dragover.prevent="$el.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20')"
                                @dragleave.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20')"
                                @drop.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20'); handleDrop($event)"
                                class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-indigo-400 transition-colors">
                                <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                                    Arrastra las fotos aquí o <span class="text-indigo-600 font-medium">selecciona archivos</span>
                                </p>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG o WebP — Max 5MB c/u</p>
                                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                                    x-ref="fileInput" @change="handleFiles($event)" class="hidden">
                            </div>

                            <template x-if="previews.length > 0">
                                <div class="grid grid-cols-5 gap-2">
                                    <template x-for="(preview, index) in previews" :key="index">
                                        <div class="relative aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 ring-1 ring-gray-200 dark:ring-gray-600">
                                            <img :src="preview" class="w-full h-full object-cover">
                                            <button type="button" @click="removeFile(index)"
                                                class="absolute top-0.5 right-0.5 rounded-full bg-red-500 text-white p-0.5 shadow hover:bg-red-600 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <button type="submit" x-show="previews.length > 0"
                                class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                Subir <span x-text="previews.length"></span> foto(s)
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Timeline</h2>
                    <div class="space-y-4">
                        @forelse($workOrder->timeline ?? [] as $event)
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full bg-indigo-600"></div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ ucwords(str_replace('_', ' ', $event['estado'])) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($event['fecha'])->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $event['usuario'] }}</p>
                                @if($event['comentario'])
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-200">{{ $event['comentario'] }}</p>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No hay eventos registrados.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Estado y Asignación</h2>
                    <div class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado Actual</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-medium
                                    @if($workOrder->status === 'recibida') bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400
                                    @elseif($workOrder->status === 'en_espera') bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400
                                    @elseif($workOrder->status === 'en_revision') bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400
                                    @elseif($workOrder->status === 'diagnosticada') bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400
                                    @elseif($workOrder->status === 'cotizacion_enviada') bg-cyan-50 text-cyan-700 dark:bg-cyan-900/20 dark:text-cyan-400
                                    @elseif($workOrder->status === 'cotizacion_aprobada') bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400
                                    @elseif($workOrder->status === 'en_reparacion') bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400
                                    @elseif($workOrder->status === 'reparada') bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400
                                    @elseif($workOrder->status === 'terminada') bg-gray-50 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400
                                    @else bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400
                                    @endif">
                                    {{ ucwords(str_replace('_', ' ', $workOrder->status)) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Prioridad</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                                    @if($workOrder->priority === 'baja') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($workOrder->priority === 'media') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                    @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @endif">
                                    {{ ucfirst($workOrder->priority) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Técnico Asignado</dt>
                            <dd class="mt-1">
                                @if($workOrder->assignedTechnician)
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400 px-3 py-1 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ $workOrder->assignedTechnician->name }}
                                    </span>
                                    <form method="POST" action="{{ route('work_orders.unassign_technician', $workOrder) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-500 hover:underline">Desasignar</button>
                                    </form>
                                </div>
                                @else
                                <form method="POST" action="{{ route('work_orders.assign_technician', $workOrder) }}" class="flex gap-2 mt-1">
                                    @csrf
                                    <select name="assigned_to" required
                                        class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                                        <option value="">Seleccionar técnico...</option>
                                        @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                        class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500 transition-colors whitespace-nowrap">
                                        Asignar
                                    </button>
                                </form>
                                @endif
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            @can('quotes.view')
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Cotización</h3>
                    @if($workOrder->quote)
                        @php $quote = $workOrder->quote; @endphp
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Estado</span>
                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium
                                    {{ $quote->status === 'aprobada' ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : '' }}
                                    {{ $quote->status === 'enviada' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                                    {{ $quote->status === 'pendiente' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400' : '' }}
                                    {{ $quote->status === 'rechazada' ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' : '' }}">
                                    {{ ucfirst($quote->status) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100">${{ number_format($quote->total, 2) }}</span>
                            </div>
                            <div class="pt-2 space-y-2">
                                <a href="{{ route('quotes.show', $workOrder) }}"
                                    class="block w-full text-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                    {{ $quote->quoteItems->count() > 0 ? 'Ver cotización' : 'Crear cotización' }}
                                </a>
                                @if($quote->status === 'aprobada' && $cashRegister)
                                <a href="{{ route('pos.index', ['work_order_id' => $workOrder->id]) }}"
                                    class="block w-full text-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-colors">
                                    Cobrar desde POS
                                </a>
                                @elseif($quote->status === 'aprobada' && !$cashRegister)
                                <span class="block w-full text-center rounded-md bg-gray-300 dark:bg-gray-600 px-3 py-2 text-sm font-semibold text-gray-500 dark:text-gray-400 cursor-not-allowed"
                                    title="Abre una caja primero para poder cobrar">
                                    Cobrar desde POS — Sin caja abierta
                                </span>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">No hay cotización creada.</p>
                        <a href="{{ route('quotes.show', $workOrder) }}"
                            class="block w-full text-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                            Crear cotización
                        </a>
                    @endif
                </div>
            </div>
            @endcan

            @can('work_orders.change_status')
            @if($workOrder->status !== 'terminada' && $workOrder->status !== 'cancelada')
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Cambiar Estado</h3>
                    <form method="POST" action="{{ route('work_orders.change_status', $workOrder) }}" class="space-y-3">
                        @csrf
                        <select name="status" required
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <option value="">Seleccionar nuevo estado...</option>
                            <option value="en_espera">En Espera</option>
                            <option value="en_revision">En Revisión</option>
                            <option value="diagnosticada">Diagnosticada</option>
                            <option value="en_reparacion">En Reparación</option>
                            <option value="reparada">Reparada</option>
                            <option value="terminada">Terminada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                        <textarea name="comment" rows="2" placeholder="Comentario (opcional)"
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"></textarea>
                        <button type="submit"
                            class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                            Actualizar estado
                        </button>
                    </form>
                </div>
            </div>
            @endif
            @endcan

            @can('work_orders.set_priority')
            @if($workOrder->status !== 'terminada' && $workOrder->status !== 'cancelada')
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Cambiar Prioridad</h3>
                    <form method="POST" action="{{ route('work_orders.set_priority', $workOrder) }}" class="space-y-3">
                        @csrf
                        <div class="flex gap-2">
                            <button type="submit" name="priority" value="baja"
                                class="flex-1 inline-flex justify-center items-center rounded-md px-3 py-2 text-sm font-semibold transition-colors
                                {{ $workOrder->priority === 'baja' ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 hover:bg-green-100' }}">
                                Baja
                            </button>
                            <button type="submit" name="priority" value="media"
                                class="flex-1 inline-flex justify-center items-center rounded-md px-3 py-2 text-sm font-semibold transition-colors
                                {{ $workOrder->priority === 'media' ? 'bg-yellow-600 text-white' : 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 hover:bg-yellow-100' }}">
                                Media
                            </button>
                            <button type="submit" name="priority" value="alta"
                                class="flex-1 inline-flex justify-center items-center rounded-md px-3 py-2 text-sm font-semibold transition-colors
                                {{ $workOrder->priority === 'alta' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 hover:bg-red-100' }}">
                                Alta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
            @endcan

            @can('work_orders.add_notes')
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Agregar Anotación</h3>
                    <form method="POST" action="{{ route('work_orders.add_note', $workOrder) }}" class="space-y-3">
                        @csrf
                        <textarea name="comment" rows="3" required placeholder="Escribir anotación..."
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"></textarea>
                        <button type="submit"
                            class="w-full inline-flex justify-center items-center rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 transition-colors">
                            Agregar anotación
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('imageUploader', () => ({
        previews: [],
        files: [],
        handleFiles(event) {
            const newFiles = Array.from(event.target.files);
            const remaining = 5 - this.files.length;
            const toAdd = newFiles.slice(0, remaining);
            toAdd.forEach(file => {
                if (!file.type.match(/image\/(jpeg|png|webp)/)) return;
                if (file.size > 5 * 1024 * 1024) return;
                this.files.push(file);
                const reader = new FileReader();
                reader.onload = (e) => this.previews.push(e.target.result);
                reader.readAsDataURL(file);
            });
            if (newFiles.length > remaining) {
                alert('Solo puedes subir hasta 5 fotos por carga.');
            }
        },
        handleDrop(event) {
            const newFiles = Array.from(event.dataTransfer.files);
            const remaining = 5 - this.files.length;
            const toAdd = newFiles.slice(0, remaining);
            toAdd.forEach(file => {
                if (!file.type.match(/image\/(jpeg|png|webp)/)) return;
                if (file.size > 5 * 1024 * 1024) return;
                this.files.push(file);
                const reader = new FileReader();
                reader.onload = (e) => this.previews.push(e.target.result);
                reader.readAsDataURL(file);
            });
            if (newFiles.length > remaining) {
                alert('Solo puedes subir hasta 5 fotos por carga.');
            }
        },
        removeFile(index) {
            this.files.splice(index, 1);
            this.previews.splice(index, 1);
            this.$refs.fileInput.value = '';
        },
    }));
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.pattern-visualizer').forEach(canvas => {
        const patternStr = canvas.dataset.pattern;
        if (!patternStr) return;
        const ctx = canvas.getContext('2d');
        const w = canvas.width, h = canvas.height;
        const margin = 14;
        const spacingX = (w - 2 * margin) / 2;
        const spacingY = (h - 2 * margin) / 2;

        const pos = {};
        for (let r = 0; r < 3; r++) {
            for (let c = 0; c < 3; c++) {
                pos[r * 3 + c + 1] = { x: margin + c * spacingX, y: margin + r * spacingY };
            }
        }

        const pattern = patternStr.split('').map(Number);

        // Draw lines
        if (pattern.length > 1) {
            ctx.beginPath();
            ctx.moveTo(pos[pattern[0]].x, pos[pattern[0]].y);
            for (let i = 1; i < pattern.length; i++) {
                ctx.lineTo(pos[pattern[i]].x, pos[pattern[i]].y);
            }
            ctx.strokeStyle = '#4f46e5';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.stroke();
        }

        // Draw dots
        for (let i = 1; i <= 9; i++) {
            const p = pos[i];
            const active = pattern.includes(i);
            ctx.beginPath();
            ctx.arc(p.x, p.y, 5, 0, Math.PI * 2);
            ctx.fillStyle = active ? '#4f46e5' : '#fff';
            ctx.fill();
            ctx.strokeStyle = active ? '#4f46e5' : '#9ca3af';
            ctx.lineWidth = active ? 2 : 1.5;
            ctx.stroke();
        }
    });
});
</script>
@endpush

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
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($workOrder->client->notification_preference) }}</dd>
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
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $workOrder->unlock_pattern ?? '—' }}</dd>
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
                            @if($workOrder->canTransitionTo('en_espera')) <option value="en_espera">En Espera</option> @endif
                            @if($workOrder->canTransitionTo('en_revision')) <option value="en_revision">En Revisión</option> @endif
                            @if($workOrder->canTransitionTo('diagnosticada')) <option value="diagnosticada">Diagnosticada</option> @endif
                            @if($workOrder->canTransitionTo('cotizacion_enviada')) <option value="cotizacion_enviada">Cotización Enviada</option> @endif
                            @if($workOrder->canTransitionTo('cotizacion_aprobada')) <option value="cotizacion_aprobada">Cotización Aprobada</option> @endif
                            @if($workOrder->canTransitionTo('en_reparacion')) <option value="en_reparacion">En Reparación</option> @endif
                            @if($workOrder->canTransitionTo('reparada')) <option value="reparada">Reparada</option> @endif
                            @if($workOrder->canTransitionTo('terminada')) <option value="terminada">Terminada</option> @endif
                            @if($workOrder->canTransitionTo('cancelada')) <option value="cancelada">Cancelada</option> @endif
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

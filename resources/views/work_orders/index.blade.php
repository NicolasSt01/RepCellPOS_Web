@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Órdenes de Trabajo</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestión de reparaciones de equipos</p>
        </div>
        @can('work_orders.create')
        <a href="{{ route('work_orders.create') }}"
            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            Nueva orden
        </a>
        @endcan
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('work_orders.index') }}" class="space-y-3">
                <div class="flex gap-3">
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="Buscar por número, marca, modelo o cliente..."
                        class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Buscar
                    </button>
                    @if(($search ?? false) || ($status ?? false) || ($priority ?? false))
                    <a href="{{ route('work_orders.index') }}"
                        class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Limpiar
                    </a>
                    @endif
                </div>
                <div class="flex gap-3 flex-wrap">
                    <select name="status" class="rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="">Todos los estados</option>
                        <option value="recibida" {{ ($status ?? '') === 'recibida' ? 'selected' : '' }}>Recibida</option>
                        <option value="en_espera" {{ ($status ?? '') === 'en_espera' ? 'selected' : '' }}>En Espera</option>
                        <option value="en_revision" {{ ($status ?? '') === 'en_revision' ? 'selected' : '' }}>En Revisión</option>
                        <option value="diagnosticada" {{ ($status ?? '') === 'diagnosticada' ? 'selected' : '' }}>Diagnosticada</option>
                        <option value="cotizacion_enviada" {{ ($status ?? '') === 'cotizacion_enviada' ? 'selected' : '' }}>Cotización Enviada</option>
                        <option value="cotizacion_aprobada" {{ ($status ?? '') === 'cotizacion_aprobada' ? 'selected' : '' }}>Cotización Aprobada</option>
                        <option value="en_reparacion" {{ ($status ?? '') === 'en_reparacion' ? 'selected' : '' }}>En Reparación</option>
                        <option value="reparada" {{ ($status ?? '') === 'reparada' ? 'selected' : '' }}>Reparada</option>
                        <option value="terminada" {{ ($status ?? '') === 'terminada' ? 'selected' : '' }}>Terminada</option>
                        <option value="cancelada" {{ ($status ?? '') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                    <select name="priority" class="rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="">Todas las prioridades</option>
                        <option value="baja" {{ ($priority ?? '') === 'baja' ? 'selected' : '' }}>Baja</option>
                        <option value="media" {{ ($priority ?? '') === 'media' ? 'selected' : '' }}>Media</option>
                        <option value="alta" {{ ($priority ?? '') === 'alta' ? 'selected' : '' }}>Alta</option>
                    </select>
                    <select name="assigned_to" class="rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="">Todos los técnicos</option>
                        <option value="unassigned" {{ (request('assigned_to') ?? '') === 'unassigned' ? 'selected' : '' }}>Sin asignar</option>
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ (request('assigned_to') ?? '') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Número</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Equipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prioridad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Técnico</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($workOrders as $wo)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $wo->work_order_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ $wo->client->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $wo->client->phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ $wo->device_brand }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $wo->device_model }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                @if($wo->status === 'recibida') bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400
                                @elseif($wo->status === 'en_espera') bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400
                                @elseif($wo->status === 'en_revision') bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400
                                @elseif($wo->status === 'diagnosticada') bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400
                                @elseif($wo->status === 'cotizacion_enviada') bg-cyan-50 text-cyan-700 dark:bg-cyan-900/20 dark:text-cyan-400
                                @elseif($wo->status === 'cotizacion_aprobada') bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400
                                @elseif($wo->status === 'en_reparacion') bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400
                                @elseif($wo->status === 'reparada') bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400
                                @elseif($wo->status === 'terminada') bg-gray-50 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400
                                @else bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400
                                @endif">
                                {{ ucwords(str_replace('_', ' ', $wo->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                @if($wo->priority === 'baja') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                @elseif($wo->priority === 'media') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                @endif">
                                {{ ucfirst($wo->priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $wo->assignedTechnician->name ?? '—' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $wo->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('work_orders.show', $wo) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay órdenes de trabajo registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workOrders->hasPages())
        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3">
            {{ $workOrders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

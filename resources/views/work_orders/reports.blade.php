@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Reportes de Órdenes de Trabajo</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Resumen y listado detallado con filtros</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <a href="{{ route('work_orders.index') }}"
                class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Volver a órdenes
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $summary->total }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Activas</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $summary->active_count }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pendientes</p>
            <p class="text-2xl font-bold text-slate-600 dark:text-slate-400 mt-1">{{ $summary->pending_count }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Completadas</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $summary->completed_count }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Canceladas</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $summary->cancelled_count }}</p>
        </div>
    </div>

    @if($byTechnician->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Órdenes Activas por Técnico</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Técnico</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Órdenes</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php $totalActive = $byTechnician->sum('total'); @endphp
                    @foreach($byTechnician as $tw)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $tw->assignedTechnician->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white">{{ $tw->total }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $totalActive > 0 ? round(($tw->total / $totalActive) * 100) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('work_orders.reports') }}" class="space-y-3">
                <div class="flex gap-3 flex-wrap">
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    <select name="status" class="rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="">Todos los estados</option>
                        @foreach(['recibida','en_espera','en_revision','diagnosticada','cotizacion_enviada','cotizacion_aprobada','en_reparacion','reparada','terminada','cancelada'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                    <select name="priority" class="rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="">Todas las prioridades</option>
                        <option value="baja" {{ request('priority') === 'baja' ? 'selected' : '' }}>Baja</option>
                        <option value="media" {{ request('priority') === 'media' ? 'selected' : '' }}>Media</option>
                        <option value="alta" {{ request('priority') === 'alta' ? 'selected' : '' }}>Alta</option>
                    </select>
                    <select name="assigned_to" class="rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="">Todos los técnicos</option>
                        <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Sin asignar</option>
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ request('assigned_to') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        Filtrar
                    </button>
                    @if(request()->anyFilled(['date_from','date_to','status','priority','assigned_to']))
                    <a href="{{ route('work_orders.reports') }}"
                        class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Limpiar
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Equipo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Prioridad</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Técnico</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Creada</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actualización</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($workOrders as $wo)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $wo->work_order_number }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $wo->client->name }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ $wo->device_brand }} {{ $wo->device_model }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                @if($wo->status === 'recibida') bg-blue-50 text-blue-700
                                @elseif($wo->status === 'en_espera') bg-yellow-50 text-yellow-700
                                @elseif($wo->status === 'en_revision') bg-purple-50 text-purple-700
                                @elseif($wo->status === 'diagnosticada') bg-indigo-50 text-indigo-700
                                @elseif($wo->status === 'cotizacion_enviada') bg-cyan-50 text-cyan-700
                                @elseif($wo->status === 'cotizacion_aprobada') bg-green-50 text-green-700
                                @elseif($wo->status === 'en_reparacion') bg-orange-50 text-orange-700
                                @elseif($wo->status === 'reparada') bg-emerald-50 text-emerald-700
                                @elseif($wo->status === 'terminada') bg-gray-50 text-gray-700
                                @else bg-red-50 text-red-700 @endif">
                                {{ ucwords(str_replace('_', ' ', $wo->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                @if($wo->priority === 'baja') bg-green-100 text-green-800
                                @elseif($wo->priority === 'media') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($wo->priority) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $wo->assignedTechnician->name ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $wo->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $wo->updated_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('work_orders.show', $wo) }}" class="text-indigo-600 hover:text-indigo-500">Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-sm text-gray-500">No se encontraron órdenes con los filtros seleccionados.</td>
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

@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $client->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detalle del cliente</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('clients.edit', $client) }}"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                Editar
            </a>
            <a href="{{ route('clients.index') }}"
                class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Volver
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-6">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->phone }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Correo electrónico</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Preferencia de notificación</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                            @if($client->notification_preference === 'whatsapp') bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400
                            @elseif($client->notification_preference === 'email') bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400
                            @else bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400
                            @endif">
                            {{ ucfirst($client->notification_preference) }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->notes ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Registrado</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Historial de Órdenes de Trabajo</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Número</th>
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
                        <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay órdenes de trabajo registradas para este cliente.
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

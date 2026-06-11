@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.tenants.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    @if($tenant->logo)
                    <img src="{{ route('r2.serve', ['path' => $tenant->logo]) }}" alt="" class="w-10 h-10 rounded-lg object-contain bg-gray-100 dark:bg-gray-700">
                    @else
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                        {{ substr($tenant->name, 0, 2) }}
                    </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $tenant->name }}</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $tenant->slug }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium
                {{ $tenant->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' }}">
                {{ $tenant->is_active ? 'Activo' : 'Inactivo' }}
            </span>
            <form method="POST" action="{{ route('admin.tenants.toggle-status', $tenant) }}" class="inline" onsubmit="return confirm('{{ $tenant->is_active ? 'Desactivar' : 'Activar' }} el tenant {{ $tenant->name }}?')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition-colors
                        {{ $tenant->is_active ? 'bg-red-600 hover:bg-red-500' : 'bg-green-600 hover:bg-green-500' }}">
                    {{ $tenant->is_active ? 'Desactivar' : 'Activar' }}
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 text-sm text-green-700 dark:text-green-400">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Información de Contacto</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $tenant->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $tenant->phone ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dirección</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $tenant->address ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Órdenes de Trabajo Recientes</h2>
                @if($recentWorkOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($recentWorkOrders as $wo)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $wo->work_order_number }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $wo->client?->name ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">
                                        {{ str_replace('_', ' ', ucfirst($wo->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $wo->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Sin órdenes de trabajo registradas.</p>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ventas Recientes</h2>
                @if($recentSales->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folio</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($recentSales as $sale)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $sale->id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${{ number_format($sale->total, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $sale->payment_method }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $sale->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Sin ventas registradas.</p>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $tenant->users_count }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Usuarios</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $tenant->clients_count }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Clientes</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $tenant->work_orders_count }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Órdenes</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $tenant->sales_count }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Ventas</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Suscripción</h2>
                @if($tenant->subscription)
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tenant->subscription->plan_type ? ucfirst($tenant->subscription->plan_type) : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</dt>
                        <dd class="mt-1">
                            @php
                                $subActive = $tenant->subscription->isActive();
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                {{ $subActive ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' }}">
                                {{ $subActive ? 'Activa' : 'Inactiva' }}
                            </span>
                        </dd>
                    </div>
                    @if($tenant->subscription->start_date)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Inicio</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $tenant->subscription->start_date->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                    @if($tenant->subscription->end_date)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Finaliza</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $tenant->subscription->end_date->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                </dl>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Sin suscripción registrada.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

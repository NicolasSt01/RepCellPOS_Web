@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Uso por Tenant</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Actividad y consumo por tenant en la plataforma</p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', ['label' => 'Total Tenants', 'value' => $totalTenants, 'subtext' => 'Registrados en la plataforma', 'color' => 'indigo'])
        @include('reportes.partials.kpi-card', ['label' => 'Total Usuarios', 'value' => $totalUsers, 'subtext' => 'En todos los tenants', 'color' => 'blue'])
        @include('reportes.partials.kpi-card', ['label' => 'OT (último mes)', 'value' => $totalOT, 'subtext' => 'Órdenes de trabajo creadas', 'color' => 'green'])
        @include('reportes.partials.kpi-card', ['label' => 'Ventas (último mes)', 'value' => $totalVentas, 'subtext' => 'Ventas realizadas', 'color' => 'yellow'])
    </div>

    <!-- Tenants Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Detalle por Tenant</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuarios</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">OT (último mes)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ventas (último mes)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Última Actividad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($tenants as $tenant)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $tenant->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $tenant->users_count ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $tenant->work_orders_count ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $tenant->sales_count ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <span class="px-2 py-1 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400">
                                {{ $tenant->plan_name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $status = $tenant->subscription_status ?? 'unknown';
                                $statusColors = [
                                    'active' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                                    'trial' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                                    'cancelled' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
                                    'expired' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400',
                                ];
                                $statusClass = $statusColors[$status] ?? 'bg-gray-50 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $statusClass }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $tenant->last_activity ? \App\Helpers\ReportHelper::formatDate($tenant->last_activity) : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay tenants registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

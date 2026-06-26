@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Clientes Nuevos vs Recurrentes</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Análisis de retención y adquisición de clientes</p>
        </div>
    </div>

    @include('reportes.partials.filtros', ['route' => route('reportes.retencion-clientes')])

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', ['label' => 'Clientes Nuevos', 'value' => $nuevos, 'subtext' => 'En el período', 'color' => 'blue'])
        @include('reportes.partials.kpi-card', ['label' => 'Clientes Recurrentes', 'value' => $recurrentes, 'subtext' => 'Compraron antes del período', 'color' => 'green'])
        @include('reportes.partials.kpi-card', ['label' => 'Tasa de Retención', 'value' => $tasaRetencion . '%', 'subtext' => 'Clientes que repitieron', 'color' => 'indigo'])
        @include('reportes.partials.kpi-card', ['label' => 'Total Clientes Activos', 'value' => $totalActivos, 'subtext' => 'Con al menos una compra', 'color' => 'yellow'])
    </div>

    <!-- Pie Chart -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Distribución: Nuevos vs Recurrentes</h2>
        </div>
        <div class="p-6">
            <div id="retencion-chart"></div>
        </div>
    </div>

    <!-- Clientes nuevos -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Clientes Nuevos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Primera Compra</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Gastado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($nuevosClientes as $cliente)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $cliente->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ \App\Helpers\ReportHelper::formatDate($cliente->first_purchase_date) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ \App\Helpers\ReportHelper::formatMoney($cliente->total_spent ?? 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay clientes nuevos en el período.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Clientes recurrentes -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Clientes Recurrentes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Compras</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Gastado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($recurrentesClientes as $cliente)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $cliente->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $cliente->total_compras ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ \App\Helpers\ReportHelper::formatMoney($cliente->total_spent ?? 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay clientes recurrentes en el período.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var chart = new ApexCharts(document.querySelector('#retencion-chart'), {
        chart: { type: 'pie', height: 350 },
        labels: {!! $chartLabels !!},
        series: {!! $chartData !!},
        colors: ['#6366f1', '#22c55e'],
        legend: { position: 'bottom' },
        responsive: [{ breakpoint: 480, options: { chart: { width: '100%' } } }]
    });
    chart.render();
});
</script>
@endpush

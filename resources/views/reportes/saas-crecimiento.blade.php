@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Crecimiento, Churn y Tenants Activos</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Evolución de la base de tenants en el tiempo</p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', ['label' => 'Tenants Activos', 'value' => $activeTenants, 'subtext' => 'Con suscripción vigente', 'color' => 'green'])
        @include('reportes.partials.kpi-card', ['label' => 'Nuevos (este mes)', 'value' => $newTenants, 'subtext' => 'Registrados en el mes', 'color' => 'blue'])
        @include('reportes.partials.kpi-card', ['label' => 'Cancelados (este mes)', 'value' => $cancelledTenants, 'subtext' => 'Dieron de baja', 'color' => 'red'])
        @include('reportes.partials.kpi-card', ['label' => 'Churn Rate', 'value' => $churnRate . '%', 'subtext' => 'Tasa de cancelación', 'color' => 'yellow'])
    </div>

    <!-- Growth Chart -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Crecimiento de Tenants (12 meses)</h2>
        </div>
        <div class="p-6">
            <div id="growth-chart"></div>
        </div>
    </div>

    <!-- Monthly Growth Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Crecimiento Mensual</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nuevos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cancelados</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Activos al Cierre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Crecimiento Neto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($monthlyGrowth as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $row->month }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <span class="text-green-600 dark:text-green-400">+{{ $row->new ?? 0 }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <span class="text-red-600 dark:text-red-400">-{{ $row->cancelled ?? 0 }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $row->active_end ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ ($row->growth ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ ($row->growth ?? 0) >= 0 ? '+' : '' }}{{ $row->growth ?? 0 }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay datos de crecimiento disponibles.
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
    var chart = new ApexCharts(document.querySelector('#growth-chart'), {
        chart: { type: 'bar', height: 350, stacked: true, toolbar: { show: true } },
        series: [
            { name: 'Activos', type: 'line', data: {!! $growthActive !!} },
            { name: 'Nuevos', type: 'column', data: {!! $growthNew !!} }
        ],
        xaxis: { categories: {!! $growthLabels !!} },
        colors: ['#22c55e', '#6366f1'],
        stroke: { width: [2, 0] },
        dataLabels: { enabled: false },
        yaxis: [{}, { opposite: true }],
        tooltip: { shared: true, intersect: false }
    });
    chart.render();
});
</script>
@endpush

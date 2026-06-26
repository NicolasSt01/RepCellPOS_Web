@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">MRR / ARR y Suscripciones</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ingresos recurrentes del SaaS</p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', ['label' => 'MRR', 'value' => \App\Helpers\ReportHelper::formatMoney($mrr), 'subtext' => 'Ingreso recurrente mensual', 'color' => 'indigo'])
        @include('reportes.partials.kpi-card', ['label' => 'ARR', 'value' => \App\Helpers\ReportHelper::formatMoney($arr), 'subtext' => 'Proyección anual', 'color' => 'blue'])
        @include('reportes.partials.kpi-card', ['label' => 'Suscripciones Activas', 'value' => $suscripcionesActivas, 'subtext' => 'Planes pagos vigentes', 'color' => 'green'])
        @include('reportes.partials.kpi-card', ['label' => 'ARPU', 'value' => \App\Helpers\ReportHelper::formatMoney($arpu), 'subtext' => 'Ingreso promedio por tenant', 'color' => 'yellow'])
    </div>

    <!-- MRR Chart -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Evolución del MRR (12 meses)</h2>
        </div>
        <div class="p-6">
            <div id="mrr-chart"></div>
        </div>
    </div>

    <!-- Plan Distribution -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Distribución por Plan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenants</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ingreso Mensual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ingreso Anual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($planDistribution as $plan)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $plan->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $plan->tenant_count ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ \App\Helpers\ReportHelper::formatMoney($plan->monthly_revenue ?? 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ \App\Helpers\ReportHelper::formatMoney($plan->annual_revenue ?? 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay planes configurados.
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
    var chart = new ApexCharts(document.querySelector('#mrr-chart'), {
        chart: { type: 'area', height: 350, toolbar: { show: true } },
        series: [{ name: 'MRR', data: {!! $mrrHistoryData !!} }],
        xaxis: { categories: {!! $mrrHistoryLabels !!}, type: 'datetime' },
        colors: ['#6366f1'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2 } },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
        yaxis: { labels: { formatter: function (v) { return '$' + v.toLocaleString(); } } },
        tooltip: { y: { formatter: function (v) { return '$' + v.toLocaleString(); } } }
    });
    chart.render();
});
</script>
@endpush

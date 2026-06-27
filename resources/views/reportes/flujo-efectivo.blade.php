@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Flujo de Efectivo Diario</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Seguimiento del flujo de efectivo día a día</p>
        </div>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.flujo-efectivo'),
        'showDateRange' => true,
    ])

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Ingresos',
            'value' => App\Helpers\ReportHelper::formatMoney($totalIngresos),
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Egresos',
            'value' => App\Helpers\ReportHelper::formatMoney($totalEgresos),
            'color' => 'red',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Saldo Neto',
            'value' => App\Helpers\ReportHelper::formatMoney($saldoNeto),
            'color' => 'indigo',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Saldo Acumulado',
            'value' => App\Helpers\ReportHelper::formatMoney($saldoAcumulado),
            'color' => 'blue',
        ])
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6 relative overflow-hidden">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Evolución Diaria</h2>
        <div id="flowChart"></div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Desglose Diario</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ingresos</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Egresos</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Neto</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Saldo Acumulado</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($dailyFlow as $flow)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $flow->date }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400">{{ App\Helpers\ReportHelper::formatMoney($flow->ingresos) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 dark:text-red-400">{{ App\Helpers\ReportHelper::formatMoney($flow->egresos) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $flow->neto >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ App\Helpers\ReportHelper::formatMoney($flow->neto) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100 font-semibold">{{ App\Helpers\ReportHelper::formatMoney($flow->saldo_acumulado) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No hay datos de flujo en el período seleccionado.</td>
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
    document.addEventListener('DOMContentLoaded', function() {
        var isDark = document.documentElement.classList.contains('dark');

        new ApexCharts(document.querySelector('#flowChart'), {
            chart: {
                type: 'line',
                height: 380,
                toolbar: { show: false },
                foreColor: isDark ? '#cbd5e1' : '#6b7280',
                zoom: { enabled: true }
            },
            series: [
                {
                    name: 'Ingresos',
                    data: {!! $flowIngresos !!}
                },
                {
                    name: 'Egresos',
                    data: {!! $flowEgresos !!}
                },
                {
                    name: 'Neto',
                    data: {!! $flowNeto !!}
                }
            ],
            xaxis: {
                categories: {!! $flowLabels !!},
                labels: { rotate: -45, style: { fontSize: '11px' } }
            },
            yaxis: {
                labels: {
                    formatter: function(val) { return '$' + val.toLocaleString(); }
                }
            },
            theme: { mode: isDark ? 'dark' : 'light' },
            colors: ['#22c55e', '#ef4444', '#6366f1'],
            stroke: {
                curve: 'smooth',
                width: 2
            },
            tooltip: {
                y: {
                    formatter: function(val) { return '$' + val.toLocaleString(); }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
            }
        }).render();
    });
</script>
@endpush

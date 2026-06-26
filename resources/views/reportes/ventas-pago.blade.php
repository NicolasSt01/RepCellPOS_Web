@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Ventas por Método de Pago</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Distribución de ventas según método de pago</p>
        </div>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.ventas-pago'),
        'showDateRange' => true,
    ])

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Efectivo',
            'value' => App\Helpers\ReportHelper::formatMoney($totalEfectivo),
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Tarjeta',
            'value' => App\Helpers\ReportHelper::formatMoney($totalTarjeta),
            'color' => 'blue',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Mixto',
            'value' => App\Helpers\ReportHelper::formatMoney($totalMixto),
            'color' => 'yellow',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => '% Efectivo',
            'value' => App\Helpers\ReportHelper::porcentaje($pctEfectivo),
            'color' => 'indigo',
        ])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Distribución por Método de Pago</h2>
            <div id="paymentChart"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Desglose por Método</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Método</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Transacciones</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">%</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($paymentMethods as $method)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $method->method }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ $method->count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ App\Helpers\ReportHelper::formatMoney($method->total) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ App\Helpers\ReportHelper::porcentaje($method->percentage) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No se encontraron ventas en el período seleccionado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var isDark = document.documentElement.classList.contains('dark');

        new ApexCharts(document.querySelector('#paymentChart'), {
            chart: {
                type: 'pie',
                height: 380,
                foreColor: isDark ? '#cbd5e1' : '#6b7280',
            },
            series: {!! $paymentData !!},
            labels: {!! $paymentLabels !!},
            theme: { mode: isDark ? 'dark' : 'light' },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: { width: 300 },
                    legend: { position: 'bottom' }
                }
            }],
            tooltip: {
                y: {
                    formatter: function(val) { return '$' + val.toLocaleString(); }
                }
            }
        }).render();
    });
</script>
@endpush

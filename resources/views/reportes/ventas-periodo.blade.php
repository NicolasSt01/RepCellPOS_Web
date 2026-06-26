@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Ventas por Período</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ingresos totales, evolución diaria y comparativa con período anterior.</p>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.ventas.periodo'),
    ])

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Ingresos',
            'value' => App\Helpers\ReportHelper::formatMoney($totalIngresos),
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Ticket Promedio',
            'value' => App\Helpers\ReportHelper::formatMoney($ticketPromedio),
            'color' => 'blue',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Transacciones',
            'value' => number_format($totalTransacciones),
            'color' => 'indigo',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'vs Período Anterior',
            'value' => ($prevPeriodDiff['direction'] ?? 'neutral') === 'up' ? '+' . ($prevPeriodDiff['pct'] ?? 0) . '%' : (($prevPeriodDiff['direction'] ?? 'neutral') === 'down' ? ($prevPeriodDiff['pct'] ?? 0) . '%' : '0%'),
            'subtext' => ($prevPeriodDiff['direction'] ?? 'neutral') === 'up' ? '↑ Incremento' : (($prevPeriodDiff['direction'] ?? 'neutral') === 'down' ? '↓ Disminución' : '→ Sin cambio'),
            'color' => ($prevPeriodDiff['direction'] ?? 'neutral') === 'up' ? 'green' : (($prevPeriodDiff['direction'] ?? 'neutral') === 'down' ? 'red' : 'gray'),
        ])
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Evolución Diaria de Ventas</h2>
        @include('reportes.partials.apex-chart', [
            'chartId' => 'dailySalesChart',
            'height' => 350,
            'chartData' => json_encode([[
                'name' => 'Ingresos',
                'data' => $salesByDayData,
            ]]),
            'chartOptions' => json_encode([
                'chart' => [
                    'type' => 'line',
                    'toolbar' => ['show' => true],
                    'zoom' => ['enabled' => true],
                ],
                'xaxis' => [
                    'categories' => $salesByDayLabels,
                    'labels' => ['style' => ['colors' => '#6b7280']],
                ],
                'yaxis' => [
                    'labels' => ['style' => ['colors' => '#6b7280']],
                    'formatter' => 'function (val) { return "$" + val.toLocaleString("es-CL"); }',
                ],
                'stroke' => [
                    'curve' => 'smooth',
                    'width' => 2,
                ],
                'colors' => ['#6366f1'],
                'tooltip' => [
                    'y' => [
                        'formatter' => 'function (val) { return "$" + val.toLocaleString("es-CL"); }',
                    ],
                ],
                'grid' => [
                    'borderColor' => '#e5e7eb',
                ],
            ]),
        ])
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Desglose Diario</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ventas</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($dailySales as $day)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ App\Helpers\ReportHelper::formatDate($day->date) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right">{{ number_format($day->count) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right font-medium">{{ App\Helpers\ReportHelper::formatMoney($day->total) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay datos para el período seleccionado.</td>
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
@endpush

@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Tasa de Aprobación de Cotizaciones</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Distribución por estado, tasas de aprobación y rechazo, tiempo de respuesta.</p>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.cotizaciones.aprobacion'),
    ])

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Pendientes',
            'value' => number_format($statusCounts['pendiente'] ?? 0),
            'color' => 'yellow',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Enviadas',
            'value' => number_format($statusCounts['enviada'] ?? 0),
            'color' => 'blue',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Aprobadas',
            'value' => number_format($statusCounts['aprobada'] ?? 0),
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Rechazadas',
            'value' => number_format($statusCounts['rechazada'] ?? 0),
            'color' => 'red',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Cobradas',
            'value' => number_format($statusCounts['cobrada'] ?? 0),
            'color' => 'indigo',
        ])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Distribución por Estado</h2>
            @include('reportes.partials.apex-chart', [
                'chartId' => 'statusDonutChart',
                'height' => 320,
                'chartData' => json_encode([
                    $statusCounts['pendiente'] ?? 0,
                    $statusCounts['enviada'] ?? 0,
                    $statusCounts['aprobada'] ?? 0,
                    $statusCounts['rechazada'] ?? 0,
                    $statusCounts['cobrada'] ?? 0,
                ]),
                'chartOptions' => json_encode([
                    'chart' => [
                        'type' => 'donut',
                    ],
                    'labels' => ['Pendientes', 'Enviadas', 'Aprobadas', 'Rechazadas', 'Cobradas'],
                    'colors' => ['#eab308', '#3b82f6', '#22c55e', '#ef4444', '#6366f1'],
                    'legend' => [
                        'position' => 'bottom',
                        'labels' => ['colors' => '#6b7280'],
                    ],
                    'dataLabels' => [
                        'enabled' => true,
                        'formatter' => 'function (val, opts) { return opts.w.globals.series[opts.seriesIndex] + " (" + val.toFixed(1) + "%)"; }',
                    ],
                    'plotOptions' => [
                        'pie' => [
                            'donut' => [
                                'labels' => [
                                    'show' => true,
                                    'total' => [
                                        'show' => true,
                                        'label' => 'Total',
                                        'formatter' => 'function (w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0); }',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responsive' => [[
                        'breakpoint' => 480,
                        'options' => [
                            'chart' => ['width' => '100%'],
                            'legend' => ['position' => 'bottom'],
                        ],
                    ]],
                ]),
            ])
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Indicadores Clave</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Cotizaciones</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($totalQuotes) }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <span class="text-sm text-green-600 dark:text-green-400">Tasa de Aprobación</span>
                    <span class="text-lg font-bold text-green-700 dark:text-green-300">{{ $tasaAprobacion }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <span class="text-sm text-red-600 dark:text-red-400">Tasa de Rechazo</span>
                    <span class="text-lg font-bold text-red-700 dark:text-red-300">{{ $tasaRechazo }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cotizaciones</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">OT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Creada</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actualizada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        $estadoClasses = [
                            'pendiente' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                            'enviada' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                            'aprobada' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                            'rechazada' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                            'cobrada' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300',
                        ];
                    @endphp
                    @forelse($quotesList as $quote)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $quote->id }}</td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $quote->work_order_number }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $quote->client }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right font-medium">{{ App\Helpers\ReportHelper::formatMoney($quote->total) }}</td>
                        <td class="px-6 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $estadoClasses[$quote->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ ucfirst($quote->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap text-right">{{ App\Helpers\ReportHelper::formatDate($quote->created_at) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap text-right">{{ App\Helpers\ReportHelper::formatDate($quote->updated_at) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay cotizaciones en el período seleccionado.</td>
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

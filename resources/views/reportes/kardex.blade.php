@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Kardex Consolidado</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Movimientos de inventario consolidados</p>
        </div>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.kardex'),
        'showDateRange' => true,
    ])

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Entradas',
            'value' => $totalEntradas,
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Salidas',
            'value' => $totalSalidas,
            'color' => 'red',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Ajustes',
            'value' => $totalAjustes,
            'color' => 'yellow',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Movimientos Totales',
            'value' => $totalMovements,
            'color' => 'indigo',
        ])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Distribución por Tipo</h2>
            <div id="typeChart"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Movimientos Recientes</h2>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cant.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock Ant.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock Res.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($movements as $movement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ App\Helpers\ReportHelper::formatDate($movement->created_at) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $movement->product_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $typeClass = match($movement->type) {
                                        'entrada', 'in' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                        'salida', 'out' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                        default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeClass }}">
                                    {{ $movement->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ $movement->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">{{ $movement->previous_stock }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100 font-medium">{{ $movement->resulting_stock }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $movement->user_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $movement->notes }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No hay movimientos en el período seleccionado.</td>
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

        new ApexCharts(document.querySelector('#typeChart'), {
            chart: {
                type: 'donut',
                height: 320,
                foreColor: isDark ? '#cbd5e1' : '#6b7280',
            },
            series: {!! $typeData !!},
            labels: {!! $typeLabels !!},
            theme: { mode: isDark ? 'dark' : 'light' },
            colors: ['#22c55e', '#ef4444', '#eab308'],
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: { width: 300 },
                    legend: { position: 'bottom' }
                }
            }],
            tooltip: {
                y: {
                    formatter: function(val) { return val + ' movimientos'; }
                }
            }
        }).render();
    });
</script>
@endpush

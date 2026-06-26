@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Top Productos y Categorías</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Análisis de ventas por producto y categoría</p>
        </div>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.ventas-productos'),
        'showDateRange' => true,
    ])

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Productos Vendidos',
            'value' => $totalProductosVendidos,
            'color' => 'indigo',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Productos Únicos Vendidos',
            'value' => $productosUnicos,
            'color' => 'blue',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Categorías Más Activas',
            'value' => $categoriasActivas,
            'color' => 'green',
        ])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Top 10 Productos por Ingresos</h2>
            <div id="productChart"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ventas por Categoría</h2>
            <div id="categoryChart"></div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Top 20 Productos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad Vendida</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Ingresos</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">% del Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($topProducts as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->category_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ $product->total_qty }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ App\Helpers\ReportHelper::formatMoney($product->total_revenue) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($product->percentage, 1) }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No se encontraron productos en el período seleccionado.</td>
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

        new ApexCharts(document.querySelector('#productChart'), {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false },
                foreColor: isDark ? '#cbd5e1' : '#6b7280',
            },
            series: [{
                name: 'Ingresos',
                data: {!! $productData !!}
            }],
            xaxis: {
                categories: {!! $productLabels !!},
                labels: { rotate: -45, style: { fontSize: '11px' } }
            },
            yaxis: {
                labels: {
                    formatter: function(val) { return '$' + val.toLocaleString(); }
                }
            },
            theme: { mode: isDark ? 'dark' : 'light' },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                }
            },
            colors: ['#6366f1'],
            tooltip: {
                y: {
                    formatter: function(val) { return '$' + val.toLocaleString(); }
                }
            }
        }).render();

        new ApexCharts(document.querySelector('#categoryChart'), {
            chart: {
                type: 'pie',
                height: 350,
                foreColor: isDark ? '#cbd5e1' : '#6b7280',
            },
            series: {!! $categoryData !!},
            labels: {!! $categoryLabels !!},
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

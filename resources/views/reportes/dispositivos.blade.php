@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Dispositivos Más Reparados</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Análisis de dispositivos atendidos en órdenes de trabajo</p>
        </div>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.dispositivos'),
        'showDateRange' => true,
    ])

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Total OT',
            'value' => $totalOT,
            'color' => 'indigo',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Marcas Únicas',
            'value' => $marcasUnicas,
            'color' => 'blue',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Modelos Únicos',
            'value' => $modelosUnicos,
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Marca Top',
            'value' => $marcaTop,
            'color' => 'yellow',
        ])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6 relative overflow-hidden">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Top Marcas</h2>
            <div id="brandChart"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Top 20 Dispositivos</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Marca</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Modelo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">OT</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Problemas Frecuentes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($devices as $device)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $device->device_brand }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $device->device_model }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ $device->count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $device->problem_summary }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No se encontraron dispositivos en el período seleccionado.</td>
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

        new ApexCharts(document.querySelector('#brandChart'), {
            chart: {
                type: 'bar',
                height: 400,
                toolbar: { show: false },
                foreColor: isDark ? '#cbd5e1' : '#6b7280',
            },
            series: [{
                name: 'Órdenes de Trabajo',
                data: {!! $brandData !!}
            }],
            xaxis: {
                categories: {!! $brandLabels !!},
                labels: { style: { fontSize: '11px' } }
            },
            theme: { mode: isDark ? 'dark' : 'light' },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                }
            },
            colors: ['#6366f1'],
            tooltip: {
                y: {
                    formatter: function(val) { return val + ' OT'; }
                }
            }
        }).render();
    });
</script>
@endpush

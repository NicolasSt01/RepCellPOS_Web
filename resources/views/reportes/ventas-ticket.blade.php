@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Ticket Promedio y Devoluciones</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Análisis de ticket promedio y devoluciones</p>
        </div>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.ventas-ticket'),
        'showDateRange' => true,
    ])

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Ticket Promedio',
            'value' => App\Helpers\ReportHelper::formatMoney($ticketPromedio),
            'color' => 'indigo',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Ticket Prom. Sin Devoluciones',
            'value' => App\Helpers\ReportHelper::formatMoney($ticketPromNeto),
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Devuelto',
            'value' => App\Helpers\ReportHelper::formatMoney($totalDevuelto),
            'color' => 'red',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => '% Devuelto',
            'value' => App\Helpers\ReportHelper::porcentaje($pctDevuelto),
            'color' => 'yellow',
        ])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Devoluciones Recientes</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Venta</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Motivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($returns as $return)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $return->sale_id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ App\Helpers\ReportHelper::formatMoney($return->refund_total) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $return->reason }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ App\Helpers\ReportHelper::formatDate($return->created_at) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No hay devoluciones en el período seleccionado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Desglose Mensual</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mes</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ventas</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Devoluciones</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Dev.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Neto</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($monthlyData as $month)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $month->month }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ $month->sales_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ App\Helpers\ReportHelper::formatMoney($month->total) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ $month->returns_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ App\Helpers\ReportHelper::formatMoney($month->return_total) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100 font-semibold">{{ App\Helpers\ReportHelper::formatMoney($month->net) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No hay datos en el período seleccionado.</td>
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
@endpush

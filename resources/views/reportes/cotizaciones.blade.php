@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Conversión de Cotizaciones</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tasa de cierre: enviadas vs aprobadas, monto cotizado vs cobrado.</p>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.taller.cotizaciones'),
    ])

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Tasa de Conversión',
            'value' => App\Helpers\ReportHelper::porcentaje($aprobadas, $totalCotizaciones),
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Aprobado',
            'value' => App\Helpers\ReportHelper::formatMoney($totalAprobado),
            'color' => 'blue',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Cobrado',
            'value' => App\Helpers\ReportHelper::formatMoney($totalCobrado),
            'color' => 'indigo',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Tasa de Cierre',
            'value' => App\Helpers\ReportHelper::porcentaje($cobradas, $totalCotizaciones),
            'color' => 'green',
        ])
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Embudo de Conversión</h2>
        <div class="flex flex-col sm:flex-row items-end gap-2">
            @php
                $steps = [
                    ['label' => 'Totales', 'count' => $totalCotizaciones, 'color' => 'bg-gray-400 dark:bg-gray-500'],
                    ['label' => 'Enviadas', 'count' => $enviadas, 'color' => 'bg-blue-400 dark:bg-blue-500'],
                    ['label' => 'Aprobadas', 'count' => $aprobadas, 'color' => 'bg-green-400 dark:bg-green-500'],
                    ['label' => 'Cobradas', 'count' => $cobradas, 'color' => 'bg-indigo-400 dark:bg-indigo-500'],
                ];
                $maxCount = max(1, $totalCotizaciones);
            @endphp
            @foreach($steps as $step)
            <div class="flex-1 text-center">
                <div class="relative mx-auto rounded-lg transition-all duration-300 flex items-center justify-center"
                     style="height: {{ max(40, ($step['count'] / $maxCount) * 120) }}px; background-color: {{ match($step['color']) {
                         'bg-gray-400 dark:bg-gray-500' => '#9ca3af',
                         'bg-blue-400 dark:bg-blue-500' => '#60a5fa',
                         'bg-green-400 dark:bg-green-500' => '#34d399',
                         'bg-indigo-400 dark:bg-indigo-500' => '#818cf8',
                         default => '#9ca3af',
                     } }};">
                    <span class="text-white font-bold text-lg drop-shadow-sm">{{ number_format($step['count']) }}</span>
                </div>
                <p class="mt-1 text-xs font-medium text-gray-600 dark:text-gray-400">{{ $step['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cotizaciones del Período</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">OT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        $statusClasses = [
                            'pendiente' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                            'enviada' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                            'aprobada' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                            'rechazada' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                            'cobrada' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300',
                        ];
                    @endphp
                    @forelse($quotes as $quote)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $quote->work_order_number }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $quote->client }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right font-medium">{{ App\Helpers\ReportHelper::formatMoney($quote->total) }}</td>
                        <td class="px-6 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$quote->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ ucfirst($quote->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap text-right">{{ App\Helpers\ReportHelper::formatDate($quote->created_at) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay cotizaciones en el período seleccionado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

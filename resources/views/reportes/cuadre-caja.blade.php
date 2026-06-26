@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Cuadre de Caja por Turno</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Verificación de efectivo por turno, descuadres e incidentes.</p>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.caja.cuadre'),
    ])

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Turnos',
            'value' => number_format($totalTurnos),
            'color' => 'blue',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Esperado',
            'value' => App\Helpers\ReportHelper::formatMoney($totalEsperado),
            'color' => 'indigo',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Real',
            'value' => App\Helpers\ReportHelper::formatMoney($totalReal),
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Descuadre Total',
            'value' => App\Helpers\ReportHelper::formatMoney($totalDescuadre),
            'subtext' => $totalDescuadre != 0 ? ($totalDescuadre > 0 ? 'Sobrante' : 'Faltante') : 'Sin descuadre',
            'color' => $totalDescuadre == 0 ? 'green' : ($totalDescuadre > 0 ? 'yellow' : 'red'),
        ])
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Turnos Cerrados</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Apertura</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Esperado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Real</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diferencia</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Incidentes</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Abierto</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cerrado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($registros as $reg)
                    @php
                        $diffColor = $reg->difference == 0 ? 'text-gray-700 dark:text-gray-300' : ($reg->difference > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400');
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $reg->user_name }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right">{{ App\Helpers\ReportHelper::formatMoney($reg->opening_amount) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right">{{ App\Helpers\ReportHelper::formatMoney($reg->expected_amount) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right">{{ App\Helpers\ReportHelper::formatMoney($reg->closing_amount) }}</td>
                        <td class="px-6 py-3 text-sm whitespace-nowrap text-right font-medium {{ $diffColor }}">{{ App\Helpers\ReportHelper::formatMoney($reg->difference) }}</td>
                        <td class="px-6 py-3 whitespace-nowrap text-center">
                            @if($reg->incidents_count > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">
                                {{ $reg->incidents_count }}
                            </span>
                            @else
                            <span class="text-gray-400 dark:text-gray-500 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap text-right">{{ App\Helpers\ReportHelper::formatDate($reg->opened_at, 'd/m/Y H:i') }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap text-right">{{ App\Helpers\ReportHelper::formatDate($reg->closed_at, 'd/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay turnos cerrados en el período seleccionado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

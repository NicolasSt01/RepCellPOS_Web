@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Ciclo de Vida / Cuellos de Botella</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tiempos promedio por etapa del proceso</p>
        </div>
    </div>

    @include('reportes.partials.filtros', ['route' => route('reportes.ciclo-vida')])

    @if(isset($hasData) && !$hasData)
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl p-8 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Próximamente</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
            Este reporte estará disponible una vez que se implemente el módulo de trazabilidad de tiempos.
            La tabla <code class="text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-1 rounded">work_order_status_history</code> aún no está disponible.
        </p>
    </div>
    @else
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', ['label' => 'Tiempo Promedio Total', 'value' => $avgTotal . 'h', 'subtext' => 'Promedio general por OT', 'color' => 'indigo'])
        @include('reportes.partials.kpi-card', ['label' => 'Tiempo en Reparación', 'value' => $avgReparacion . 'h', 'subtext' => 'Promedio en taller', 'color' => 'blue'])
        @include('reportes.partials.kpi-card', ['label' => 'OT Completadas', 'value' => $totalCompletadas, 'subtext' => 'En el período', 'color' => 'green'])
        @include('reportes.partials.kpi-card', ['label' => 'Tiempo en Cotización', 'value' => $avgCotizacion . 'h', 'subtext' => 'Promedio en cotización', 'color' => 'yellow'])
    </div>

    <!-- Timeline / Status History -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Historial de Estados por OT</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">OT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado Anterior</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado Nuevo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tiempo en Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($statusHistory as $hist)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            #{{ $hist->work_order_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $hist->from_status ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400">
                                {{ $hist->to_status ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ isset($hist->duration_hours) ? $hist->duration_hours . 'h' : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ \App\Helpers\ReportHelper::formatDate($hist->created_at) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay historial de cambios en el período.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Productividad por Técnico</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rendimiento individual: OT completadas, en proceso y carga activa.</p>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.taller.productividad'),
    ])

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Técnicos Activos',
            'value' => $totalTecnicos,
            'color' => 'blue',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'OT Completadas',
            'value' => number_format($totalCompletadas),
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'OT en Proceso',
            'value' => number_format($totalEnProceso),
            'color' => 'yellow',
        ])
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($technicians as $tech)
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-sm truncate">{{ $tech->name }}</h3>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Total OT</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $tech->total }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Completadas</dt>
                    <dd class="font-medium text-green-600 dark:text-green-400">{{ $tech->completadas }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">En Proceso</dt>
                    <dd class="font-medium text-yellow-600 dark:text-yellow-400">{{ $tech->enProceso }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Activas ahora</dt>
                    <dd class="font-medium text-indigo-600 dark:text-indigo-400">{{ $tech->activas }}</dd>
                </div>
            </dl>
        </div>
        @empty
        <div class="col-span-full text-center py-8 text-sm text-gray-500 dark:text-gray-400">
            No hay técnicos registrados en el período.
        </div>
        @endforelse
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Detalle por Técnico</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Técnico</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total OT</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completadas</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">En Proceso</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Activas ahora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($technicians as $tech)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $tech->name }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right">{{ $tech->total }}</td>
                        <td class="px-6 py-3 text-sm text-green-600 dark:text-green-400 whitespace-nowrap text-right font-medium">{{ $tech->completadas }}</td>
                        <td class="px-6 py-3 text-sm text-yellow-600 dark:text-yellow-400 whitespace-nowrap text-right">{{ $tech->enProceso }}</td>
                        <td class="px-6 py-3 text-sm text-indigo-600 dark:text-indigo-400 whitespace-nowrap text-right">{{ $tech->activas }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay datos para el período seleccionado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

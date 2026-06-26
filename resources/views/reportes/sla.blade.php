@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">SLA / Órdenes Atrasadas</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitoreo de promesas, vencimientos y cumplimiento</p>
        </div>
    </div>

    @include('reportes.partials.filtros', ['route' => route('reportes.sla')])

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', ['label' => 'OT Activas', 'value' => $totalActivas, 'subtext' => 'Órdenes en taller', 'color' => 'blue'])
        @include('reportes.partials.kpi-card', ['label' => 'OT Vencidas', 'value' => $totalVencidas, 'subtext' => 'Promesa incumplida', 'color' => 'red'])
        @include('reportes.partials.kpi-card', ['label' => 'OT sin Promesa', 'value' => $totalSinPromesa, 'subtext' => 'Sin fecha prometida', 'color' => 'yellow'])
        @include('reportes.partials.kpi-card', ['label' => 'Cumplimiento', 'value' => $cumplimiento . '%', 'subtext' => 'Índice general', 'color' => 'green'])
    </div>

    <!-- Próximas a vencer -->
    @if($proximas->count() > 0)
    <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700 rounded-xl p-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <h2 class="text-sm font-semibold text-amber-800 dark:text-amber-300">Órdenes próximas a vencer ({{ $proximas->count() }})</h2>
        </div>
        <div class="mt-2 space-y-1">
            @foreach($proximas as $ot)
            <p class="text-xs text-amber-700 dark:text-amber-400">
                #{{ $ot->id }} — {{ $ot->status ?? '—' }}
                @if($ot->promised_at) · Vence {{ \App\Helpers\ReportHelper::formatDate($ot->promised_at) }}@endif
            </p>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Órdenes Vencidas -->
    @if($vencidas->count() > 0)
    <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-700 rounded-xl p-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <h2 class="text-sm font-semibold text-red-800 dark:text-red-300">Órdenes vencidas — requieren atención inmediata</h2>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">OT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prometida</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Días vencida</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($vencidas as $ot)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            #{{ $ot->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-md bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400">
                                {{ $ot->status ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $ot->promised_at ? \App\Helpers\ReportHelper::formatDate($ot->promised_at) : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">
                            {{ $ot->promised_at ? now()->diffInDays($ot->promised_at) : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay órdenes vencidas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-700 rounded-xl p-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-green-800 dark:text-green-300">No hay órdenes vencidas. ¡Cumplimiento al día!</p>
        </div>
    </div>
    @endif
</div>
@endsection

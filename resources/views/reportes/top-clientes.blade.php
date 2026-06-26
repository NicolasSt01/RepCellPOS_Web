@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Top Clientes</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Clientes con mayor actividad y gasto</p>
        </div>
    </div>

    @include('reportes.partials.filtros', [
        'route' => route('reportes.top-clientes'),
        'showDateRange' => true,
    ])

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Clientes Activos',
            'value' => $totalClientes,
            'color' => 'indigo',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Total Gastado',
            'value' => App\Helpers\ReportHelper::formatMoney($totalGastado),
            'color' => 'green',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Cliente Top',
            'value' => $clienteTop,
            'color' => 'yellow',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Ticket Promedio por Cliente',
            'value' => App\Helpers\ReportHelper::formatMoney($ticketPromedioCliente),
            'color' => 'blue',
        ])
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Top 30 Clientes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teléfono</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total OT</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Compras</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Gastado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Última Compra</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($clients as $client)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $client->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $client->phone }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ $client->total_work_orders }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ $client->total_purchases }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ App\Helpers\ReportHelper::formatMoney($client->total_spent) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">{{ App\Helpers\ReportHelper::formatDate($client->last_purchase_date) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No se encontraron clientes en el período seleccionado.</td>
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

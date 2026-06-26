@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rotación de Inventario y Obsoletos</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Productos con baja rotación y capital inmovilizado</p>
        </div>
    </div>

    @include('reportes.partials.filtros', ['route' => route('reportes.rotacion')])

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', ['label' => 'Rotación General', 'value' => $rotacionGeneral, 'subtext' => 'Veces rotadas en el período', 'color' => 'indigo'])
        @include('reportes.partials.kpi-card', ['label' => 'Días de Inventario', 'value' => $diasInventario, 'subtext' => 'Días para agotar stock actual', 'color' => 'blue'])
        @include('reportes.partials.kpi-card', ['label' => 'Productos sin Movimiento', 'value' => $productosSinMovimiento, 'subtext' => 'Sin ventas en el período', 'color' => 'red'])
        @include('reportes.partials.kpi-card', ['label' => 'Capital Inmovilizado', 'value' => \App\Helpers\ReportHelper::formatMoney($capitalInmovilizado), 'subtext' => 'Valor de productos sin rotación', 'color' => 'yellow'])
    </div>

    <!-- Productos sin movimiento -->
    @if($sinMovimiento->count() > 0)
    <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-700 rounded-xl p-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
            <p class="text-sm text-red-800 dark:text-red-300">
                <strong>{{ $productosSinMovimiento }}</strong> producto(s) sin movimiento en el período — capital inmovilizado: {{ \App\Helpers\ReportHelper::formatMoney($capitalInmovilizado) }}
            </p>
        </div>
    </div>
    @endif

    <!-- Tabla de productos -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rotación por Producto</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vendidos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rotación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Última Venta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Capital</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors {{ $product->sales_qty == 0 ? 'bg-red-50 dark:bg-red-900/5' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $product->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $product->stock ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $product->sales_qty ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ ($product->rotation ?? 0) < 1 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ number_format($product->rotation ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $product->last_sale_date ? \App\Helpers\ReportHelper::formatDate($product->last_sale_date) : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ isset($product->capital) ? \App\Helpers\ReportHelper::formatMoney($product->capital) : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay productos registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

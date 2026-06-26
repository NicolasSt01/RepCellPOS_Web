@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Valorización de Stock</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Valor total del inventario, productos bajo mínimo y reposición priorizada.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('reportes.partials.kpi-card', [
            'label' => 'Valor Total Inventario',
            'value' => App\Helpers\ReportHelper::formatMoney($valorTotal),
            'color' => 'indigo',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Productos Activos',
            'value' => number_format($totalProductos),
            'color' => 'blue',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Bajo Mínimo',
            'value' => number_format($bajoMinimo),
            'color' => 'red',
        ])
        @include('reportes.partials.kpi-card', [
            'label' => 'Capital en Bajo Mínimo',
            'value' => App\Helpers\ReportHelper::formatMoney($capitalBajoMinimo),
            'color' => 'yellow',
        ])
    </div>

    @if($productosCriticos->isNotEmpty())
    <div class="space-y-3">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Alertas de Stock Crítico</h2>
        @foreach($productosCriticos as $product)
        @php
            $ratio = $product->min_stock > 0 ? $product->stock / $product->min_stock : 1;
            $isCritical = $ratio <= 0.5;
        @endphp
        <div class="rounded-lg border p-4 {{ $isCritical ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' }}">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-semibold {{ $isCritical ? 'text-red-800 dark:text-red-200' : 'text-yellow-800 dark:text-yellow-200' }}">{{ $product->name }}</h3>
                    <p class="text-xs {{ $isCritical ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400' }}">Código: {{ $product->code }}</p>
                </div>
                <span class="text-xs font-medium {{ $isCritical ? 'text-red-700 dark:text-red-300' : 'text-yellow-700 dark:text-yellow-300' }}">
                    Stock: {{ $product->stock }} / Mín: {{ $product->min_stock }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Inventario Completo</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock Mínimo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Costo Unit.</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valor Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock Disponible</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($products as $product)
                    @php
                        $isLow = $product->stock <= $product->min_stock;
                    @endphp
                    <tr class="{{ $isLow ? 'bg-red-50 dark:bg-red-900/10' : '' }} hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap font-mono">{{ $product->code }}</td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $product->name }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $product->category_name ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right {{ $isLow ? 'font-bold text-red-600 dark:text-red-400' : '' }}">{{ number_format($product->stock) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right">{{ number_format($product->min_stock) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right">{{ App\Helpers\ReportHelper::formatMoney($product->purchase_price) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right font-medium">{{ App\Helpers\ReportHelper::formatMoney($product->valor_total) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap text-right">{{ number_format($product->available_stock) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay productos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $product->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($product->type) }} — {{ $product->code ?? 'Sin código' }}</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Editar</a>
            <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Volver</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            @if($product->image_url)
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6 flex items-center justify-center">
                <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}" class="max-h-64 rounded-lg object-contain">
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Información del Producto</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Código</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->code ?? '—' }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Código de Barras</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->barcode ?? '—' }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Parte</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->part_number ?? '—' }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Categoría</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->category?->name ?? '—' }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Precio de Compra</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">${{ number_format($product->purchase_price, 2) }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Precio de Venta</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">${{ number_format($product->sale_price, 2) }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Marca Compatible</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->compatible_brand ?? '—' }}</dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Modelo Compatible</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->compatible_model ?? '—' }}</dd></div>
                        @if($product->has_tax)
                        <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Impuesto</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->tax_percentage }}%</dd></div>
                        @endif
                        <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->description ?? '—' }}</dd></div>
                    </dl>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <div class="sm:flex sm:items-center sm:justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Historial de Kardex</h2>
                        <a href="{{ route('reportes.kardex', ['product_id' => $product->id]) }}"
                           class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Ver en reportes →</a>
                    </div>

                    <form method="GET" class="flex flex-wrap items-end gap-2 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Desde</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}"
                                class="block rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Hasta</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}"
                                class="block rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 text-sm">
                        </div>
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Filtrar</button>
                        @if($dateFrom || $dateTo)
                        <a href="{{ route('products.show', $product) }}"
                            class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">Limpiar</a>
                        @endif
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Stock Ant.</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Stock Res.</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Usuario</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($product->kardexMovements as $movement)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                            @if($movement->type === 'entrada') bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400
                                            @elseif($movement->type === 'salida') bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400
                                            @else bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400
                                            @endif">
                                            {{ ucfirst($movement->type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-right text-sm text-gray-900 dark:text-gray-100">{{ $movement->quantity }}</td>
                                    <td class="px-4 py-2 text-right text-sm text-gray-500 dark:text-gray-400">{{ $movement->previous_stock }}</td>
                                    <td class="px-4 py-2 text-right text-sm text-gray-900 dark:text-gray-100">{{ $movement->resulting_stock }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $movement->user->name }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay movimientos registrados.{{ $dateFrom || $dateTo ? ' en el período seleccionado.' : '' }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @if($product->type === 'producto')
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Stock</h2>
                    <div class="text-center">
                        <p class="text-4xl font-bold {{ $product->isLowStock() ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">{{ $product->stock }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Stock mínimo: {{ $product->min_stock }}</p>
                        @if($product->isLowStock())
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 font-semibold">Stock bajo</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Ajustar Stock</h3>
                    <form method="POST" action="{{ route('products.adjust_stock', $product) }}" class="space-y-3">
                        @csrf
                        <select name="type" required class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <option value="entrada">Entrada (agregar stock)</option>
                            <option value="salida">Salida (retirar stock)</option>
                            <option value="ajuste">Ajuste (establecer stock)</option>
                        </select>
                        <input type="number" name="quantity" min="0" required placeholder="Cantidad"
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <textarea name="notes" rows="2" placeholder="Motivo del ajuste (opcional)"
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"></textarea>
                        <button type="submit" class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Ajustar stock</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

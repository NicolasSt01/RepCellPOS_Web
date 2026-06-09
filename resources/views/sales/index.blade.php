@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Historial de Ventas</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Consulta y reimpresión de tickets de venta</p>
        </div>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            Nuevo cobro
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Buscar por folio</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="# de venta o folio"
                        class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Método de pago</label>
                    <select name="payment_method"
                        class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="">Todos</option>
                        <option value="efectivo" {{ request('payment_method') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                        <option value="tarjeta_transferencia" {{ request('payment_method') === 'tarjeta_transferencia' ? 'selected' : '' }}>Tarjeta / Transferencia</option>
                        <option value="mixto" {{ request('payment_method') === 'mixto' ? 'selected' : '' }}>Mixto</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex gap-2">
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        Filtrar
                    </button>
                    <a href="{{ route('sales.index') }}" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Folio</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Atendió</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subtotal</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pago</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Devolución</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors
                        {{ $sale->return_status === 'total' ? 'bg-red-50 dark:bg-red-900/10' : '' }}
                        {{ $sale->return_status === 'parcial' ? 'bg-orange-50 dark:bg-orange-900/10' : '' }}">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">#{{ $sale->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $sale->user->name }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">${{ number_format($sale->subtotal, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100">
                            @if($sale->refunded_total > 0)
                            <span class="line-through text-gray-400 dark:text-gray-500">${{ number_format($sale->total, 2) }}</span>
                            <span class="text-red-600 dark:text-red-400 ml-1">${{ number_format($sale->total - $sale->refunded_total, 2) }}</span>
                            @else
                            ${{ number_format($sale->total, 2) }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                {{ $sale->payment_method === 'efectivo' ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : '' }}
                                {{ $sale->payment_method === 'tarjeta_transferencia' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                                {{ $sale->payment_method === 'mixto' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400' : '' }}">
                                {{ $sale->payment_method === 'efectivo' ? 'Efectivo' : ($sale->payment_method === 'tarjeta_transferencia' ? 'Tarjeta' : 'Mixto') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($sale->return_status === 'total')
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                Total
                            </span>
                            @elseif($sale->return_status === 'parcial')
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                Parcial
                            </span>
                            @else
                            <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('sales.show', $sale) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 text-sm font-medium">Detalle</a>
                                <a href="{{ route('sales.print', $sale) }}" target="_blank" class="text-gray-600 dark:text-gray-400 hover:text-gray-500 text-sm font-medium">Imprimir</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            No se encontraron ventas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

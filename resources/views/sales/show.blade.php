@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Venta #{{ $sale->id }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('sales.index') }}" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                ← Volver
            </a>
            <a href="{{ route('sales.print', $sale) }}" target="_blank" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                Imprimir ticket
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Artículos</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cant</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">P/U</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($sale->saleItems as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $item->description }}</td>
                                <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-gray-100">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Subtotal</dt><dd class="text-gray-900 dark:text-gray-100">${{ number_format($sale->subtotal, 2) }}</dd></div>
                        @if($sale->tax_total > 0)
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">IVA</dt><dd class="text-gray-900 dark:text-gray-100">${{ number_format($sale->tax_total, 2) }}</dd></div>
                        @endif
                        @if($sale->discount > 0)
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Descuento</dt><dd class="text-red-600">-${{ number_format($sale->discount, 2) }}</dd></div>
                        @endif
                        <div class="flex justify-between text-base font-bold border-t border-gray-200 dark:border-gray-700 pt-2"><dt class="text-gray-900 dark:text-gray-100">Total</dt><dd class="text-indigo-600 dark:text-indigo-400">${{ number_format($sale->total, 2) }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Información del Pago</h2>
                </div>
                <div class="p-4 space-y-3 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Método:</span>
                        <span class="ml-1 font-medium text-gray-900 dark:text-gray-100">
                            @switch($sale->payment_method)
                                @case('efectivo') Efectivo @break
                                @case('tarjeta_transferencia') Tarjeta / Transferencia @break
                                @case('mixto') Mixto (Efectivo + Tarjeta) @break
                            @endswitch
                        </span>
                    </div>
                    @if($sale->cash_amount > 0)
                    <div><span class="text-gray-500 dark:text-gray-400">Efectivo:</span> <span class="font-medium text-gray-900 dark:text-gray-100">${{ number_format($sale->cash_amount, 2) }}</span></div>
                    @endif
                    @if($sale->card_amount > 0)
                    <div><span class="text-gray-500 dark:text-gray-400">Tarjeta:</span> <span class="font-medium text-gray-900 dark:text-gray-100">${{ number_format($sale->card_amount, 2) }}</span></div>
                    @endif
                    @if($sale->payment_reference)
                    <div><span class="text-gray-500 dark:text-gray-400">Folio:</span> <span class="font-medium text-gray-900 dark:text-gray-100">{{ $sale->payment_reference }}</span></div>
                    @endif
                    @if($sale->change_amount > 0)
                    <div><span class="text-gray-500 dark:text-gray-400">Cambio:</span> <span class="font-medium text-green-600">${{ number_format($sale->change_amount, 2) }}</span></div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Datos</h2>
                </div>
                <div class="p-4 space-y-3 text-sm">
                    <div><span class="text-gray-500 dark:text-gray-400">Atendió:</span> <span class="ml-1 font-medium text-gray-900 dark:text-gray-100">{{ $sale->user->name }}</span></div>
                    @if($sale->client)
                    <div><span class="text-gray-500 dark:text-gray-400">Cliente:</span> <span class="ml-1 font-medium text-gray-900 dark:text-gray-100">{{ $sale->client->name }}</span></div>
                    @endif
                    <div><span class="text-gray-500 dark:text-gray-400">Caja:</span> <span class="ml-1 font-medium text-gray-900 dark:text-gray-100">#{{ $sale->cash_register_id }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Cotización — {{ $workOrder->work_order_number }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Estado: <span class="font-medium">{{ ucfirst($quote->status) }}</span>
            </p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            @if($quote->status === 'pendiente')
            <form method="POST" action="{{ route('quotes.send', $quote) }}">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 transition-colors">
                    Enviar al cliente
                </button>
            </form>
            @endif
            @if($quote->status === 'enviada')
            <form method="POST" action="{{ route('quotes.approve', $quote) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 transition-colors">
                    Aprobar
                </button>
            </form>
            <form method="POST" action="{{ route('quotes.reject', $quote) }}" class="inline">
                @csrf
                <input type="hidden" name="reason" value="Rechazada por el cliente">
                <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors"
                    onclick="return confirm('¿Rechazar esta cotización? La orden será cancelada.')">
                    Rechazar
                </button>
            </form>
            @endif
            <a href="{{ route('work_orders.show', $workOrder) }}" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Volver a orden
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Items de la Cotización</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Concepto</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cant.</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">P. Unit.</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Impuesto</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subtotal</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($quote->quoteItems as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $item->description }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                            {{ $item->type === 'producto' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' : 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400' }}">
                                            {{ ucfirst($item->type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">{{ $item->tax_percentage }}%</td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">${{ number_format($item->subtotal, 2) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if($quote->status === 'pendiente')
                                        <form method="POST" action="{{ route('quotes.remove_item', $item) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-500 text-sm">Eliminar</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay items en la cotización.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Resumen</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Subtotal</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">${{ number_format($quote->subtotal, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Impuestos</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">${{ number_format($quote->tax_total, 2) }}</dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between">
                            <dt class="text-base font-semibold text-gray-900 dark:text-gray-100">Total</dt>
                            <dd class="text-base font-bold text-gray-900 dark:text-gray-100">${{ number_format($quote->total, 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($quote->status === 'pendiente')
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Agregar Item</h3>
                    <form method="POST" action="{{ route('quotes.add_item', $quote) }}" class="space-y-3">
                        @csrf
                        <select name="product_id" id="product_select"
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <option value="">Seleccionar producto...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->sale_price }}" data-tax="{{ $product->tax_percentage }}" data-type="{{ $product->type }}">
                                    {{ $product->name }} — ${{ number_format($product->sale_price, 2) }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="type" id="item_type" value="producto">
                        <input type="text" name="description" id="item_description" required placeholder="Descripción del concepto"
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <div class="grid grid-cols-3 gap-2">
                            <input type="number" name="quantity" value="1" min="1" required placeholder="Cant."
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <input type="number" name="unit_price" id="unit_price" step="0.01" min="0" required placeholder="Precio"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <input type="number" name="tax_percentage" id="tax_percentage" step="0.01" min="0" max="100" value="0" placeholder="Imp.%"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        </div>
                        <button type="submit" class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                            Agregar item
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.getElementById('product_select')?.addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.value) {
        document.getElementById('unit_price').value = option.dataset.price;
        document.getElementById('tax_percentage').value = option.dataset.tax;
        document.getElementById('item_type').value = option.dataset.type;
        document.getElementById('item_description').value = option.text.split(' — ')[0];
    }
});
</script>
@endsection

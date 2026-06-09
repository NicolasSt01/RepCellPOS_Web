@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="quoteForm()">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Cotización — {{ $workOrder->work_order_number }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Cliente: <span class="font-medium">{{ $workOrder->client->name }}</span>
                &middot; Estado: <span class="font-medium">{{ ucfirst($quote->status) }}</span>
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
            @if($quote->status === 'aprobada')
            <a href="{{ route('pos.index', ['work_order_id' => $workOrder->id]) }}"
                class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-colors">
                Cobrar desde POS
            </a>
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
                                            {{ $item->type === 'producto' ? 'Producto' : 'Servicio' }}
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
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-500 text-sm font-medium">Eliminar</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Todavía no hay items en la cotización.
                                        <br>Usa el formulario de la derecha para agregar productos o servicios.
                                    </td>
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
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Agregar Concepto</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Agrega productos del inventario o servicios personalizados a la cotización.</p>

                    <form method="POST" action="{{ route('quotes.add_item', $quote) }}" class="space-y-4">
                        @csrf

                        <!-- Type Toggle -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Tipo de concepto</label>
                            <div class="flex p-0.5 bg-gray-100 dark:bg-gray-700/50 rounded-lg">
                                <button type="button" @click="itemType = 'producto'; itemProductId = ''; itemDescription = ''; clearProductSearch()"
                                    class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all"
                                    :class="itemType === 'producto' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">
                                    📦 Producto (del inventario)
                                </button>
                                <button type="button" @click="itemType = 'servicio'; itemProductId = ''"
                                    class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all"
                                    :class="itemType === 'servicio' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">
                                    🔧 Servicio (personalizado)
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="type" :value="itemType">
                        <input type="hidden" name="product_id" :value="itemProductId">

                        <!-- Product Search (only for productos) -->
                        <div x-show="itemType === 'producto'" x-collapse>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Buscar producto del inventario
                            </label>
                            <div class="relative" @click.away="showProductResults = false">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                                    </div>
                                    <input type="text" x-model="productSearch" @input.debounce="filterProducts" @focus="filterProducts" placeholder="Escribe para buscar (nombre o código)..."
                                        class="block w-full pl-9 rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                                </div>
                                <div x-show="showProductResults && filteredProducts.length > 0"
                                    class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-48 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto sm:text-sm border border-gray-200 dark:border-gray-700">
                                    <template x-for="p in filteredProducts" :key="p.id">
                                        <div @click="selectProduct(p)" class="cursor-pointer select-none relative py-2 px-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-900 dark:text-gray-100">
                                            <div class="flex justify-between items-center">
                                                <div class="min-w-0 flex-1">
                                                    <span class="font-medium truncate" x-text="p.name"></span>
                                                    <span class="text-xs text-gray-400 ml-2" x-text="p.code ? 'Cód: ' + p.code : ''"></span>
                                                </div>
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-2" x-text="'$' + parseFloat(p.sale_price).toFixed(2)"></span>
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                <span x-text="p.type === 'producto' ? 'Stock: ' + p.stock : ''"></span>
                                                <span x-show="p.tax_percentage > 0" x-text="' — IVA ' + p.tax_percentage + '%'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div x-show="showProductResults && productSearch.length > 0 && filteredProducts.length === 0"
                                    class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md py-4 text-center text-sm text-gray-500 border border-gray-200 dark:border-gray-700">
                                    No se encontraron productos con ese nombre.
                                    <br><span class="text-xs">Puedes cambiar a <strong>Servicio personalizado</strong> si lo que necesitas no está en el inventario.</span>
                                </div>
                            </div>

                            <!-- Selected product badge -->
                            <div x-show="selectedProduct" class="mt-2 flex items-center gap-2 p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                                <span class="text-xs font-medium text-indigo-700 dark:text-indigo-300 flex-1 truncate" x-text="selectedProduct?.name"></span>
                                <button type="button" @click="clearProductSearch()" class="text-xs text-red-600 hover:text-red-500 font-medium">Quitar</button>
                            </div>
                        </div>

                        <!-- Service hint (only for servicios) -->
                        <div x-show="itemType === 'servicio'" class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                            <p class="text-xs text-purple-700 dark:text-purple-300 font-medium">💡 Servicio personalizado</p>
                            <p class="text-xs text-purple-600 dark:text-purple-400 mt-0.5">Ej: Mano de obra, diagnóstico, respaldo de datos, instalación de protector, limpieza interna, etc.</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Descripción <span class="text-red-500">*</span>
                                <span class="text-gray-400 font-normal"> — ¿Qué es lo que se cobra?</span>
                            </label>
                            <input type="text" name="description" x-model="itemDescription" required
                                placeholder="Ej: Mano de obra por cambio de pantalla"
                                class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <p class="mt-0.5 text-xs text-gray-400">Sé específico — esto aparecerá en la cotización que verá el cliente.</p>
                        </div>

                        <!-- Quantity, Price, Tax grid -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Detalles del cobro</label>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-0.5">Cantidad</label>
                                    <input type="number" name="quantity" x-model="itemQuantity" min="1" required
                                        placeholder="1"
                                        class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6 text-center">
                                    <p class="mt-0.5 text-xs text-gray-400 text-center">¿Cuántas piezas?</p>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-0.5">Precio unitario</label>
                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" name="unit_price" x-model="itemPrice" step="0.01" min="0" required
                                            placeholder="0.00"
                                            class="block w-full rounded-md border-0 py-2 pl-6 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6 text-right">
                                    </div>
                                    <p class="mt-0.5 text-xs text-gray-400 text-center">Precio por unidad</p>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-0.5">IVA %</label>
                                    <input type="number" name="tax_percentage" x-model="itemTax" step="0.01" min="0" max="100"
                                        placeholder="16"
                                        class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6 text-center">
                                    <p class="mt-0.5 text-xs text-gray-400 text-center">Ej: 16</p>
                                </div>
                            </div>
                            <div x-show="itemQuantity > 0 && itemPrice > 0" class="mt-2 p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-right">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Subtotal: </span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="'$' + (itemQuantity * itemPrice).toFixed(2)"></span>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Agregar a la cotización
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($quote->status === 'pendiente' && $quote->quoteItems->count() > 0)
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">¿Listo para enviar?</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">Revisa los items y presiona <strong>"Enviar al cliente"</strong> para notificar al cliente y que pueda aprobar la cotización.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('quoteForm', () => ({
        itemType: 'producto',
        itemProductId: '',
        itemDescription: '',
        itemQuantity: 1,
        itemPrice: '',
        itemTax: 16,
        productSearch: '',
        showProductResults: false,
        selectedProduct: null,
        filteredProducts: [],
        allProducts: @json($products),

        filterProducts() {
            if (this.productSearch.length < 1) {
                this.filteredProducts = this.allProducts.slice(0, 10);
            } else {
                const s = this.productSearch.toLowerCase();
                this.filteredProducts = this.allProducts.filter(p =>
                    p.name.toLowerCase().includes(s) ||
                    (p.code && p.code.toLowerCase().includes(s)) ||
                    (p.barcode && p.barcode.toLowerCase().includes(s))
                ).slice(0, 10);
            }
            this.showProductResults = this.filteredProducts.length > 0 || this.productSearch.length > 0;
        },

        selectProduct(p) {
            this.selectedProduct = p;
            this.itemProductId = p.id;
            this.itemDescription = p.name;
            this.itemPrice = parseFloat(p.sale_price);
            this.itemTax = parseFloat(p.tax_percentage || 0);
            this.itemType = p.type;
            this.productSearch = p.name;
            this.showProductResults = false;
        },

        clearProductSearch() {
            this.selectedProduct = null;
            this.itemProductId = '';
            this.itemDescription = '';
            this.itemPrice = '';
            this.productSearch = '';
            this.filteredProducts = [];
        }
    }));
});
</script>
@endpush
@endsection
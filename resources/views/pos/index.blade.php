@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="posApp()">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Punto de Venta</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($cashRegister)
                    Caja abierta — Fondo: ${{ number_format($cashRegister->opening_amount, 2) }}
                @else
                    <span class="text-red-600 dark:text-red-400 font-semibold">No hay caja abierta</span>
                @endif
            </p>
        </div>
        @if(!$cashRegister)
        <a href="{{ route('cash_registers.index') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            Abrir caja
        </a>
        @endif
    </div>

    @if($cashRegister)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <input type="text" x-model="search" placeholder="Buscar producto por nombre o código..."
                        class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div class="p-4 max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <button @click="addToCart(product)"
                                class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="product.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="'$' + parseFloat(product.sale_price).toFixed(2)"></p>
                                <p class="text-xs text-gray-400" x-text="'Stock: ' + product.stock"></p>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Carrito</h2>
                </div>
                <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
                    <template x-if="cart.length === 0">
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Carrito vacío</p>
                    </template>
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="item.description"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span x-text="item.quantity"></span> x $<span x-text="parseFloat(item.unit_price).toFixed(2)"></span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">$<span x-text="parseFloat(item.quantity * item.unit_price).toFixed(2)"></span></span>
                                <button @click="removeFromCart(index)" class="text-red-600 dark:text-red-400 hover:text-red-500 text-xs">✕</button>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                        <span class="text-gray-900 dark:text-gray-100">$<span x-text="subtotal.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Impuestos</span>
                        <span class="text-gray-900 dark:text-gray-100">$<span x-text="taxTotal.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-base font-bold border-t border-gray-200 dark:border-gray-700 pt-2">
                        <span class="text-gray-900 dark:text-gray-100">Total</span>
                        <span class="text-gray-900 dark:text-gray-100">$<span x-text="total.toFixed(2)"></span></span>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <form method="POST" action="{{ route('pos.checkout') }}" @submit="prepareForm">
                        @csrf
                        <template x-for="(item, index) in cart" :key="index">
                            <div>
                                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                                <input type="hidden" :name="'items[' + index + '][type]'" :value="item.type">
                                <input type="hidden" :name="'items[' + index + '][description]'" :value="item.description">
                                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                                <input type="hidden" :name="'items[' + index + '][unit_price]'" :value="item.unit_price">
                                <input type="hidden" :name="'items[' + index + '][tax_percentage]'" :value="item.tax_percentage">
                            </div>
                        </template>
                        <div class="space-y-3">
                            <select name="payment_method" x-model="paymentMethod" required
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta_transferencia">Tarjeta / Transferencia</option>
                            </select>
                            <template x-if="paymentMethod === 'efectivo'">
                                <input type="number" name="amount_received" step="0.01" min="0" placeholder="Monto recibido"
                                    class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            </template>
                            <template x-if="paymentMethod === 'tarjeta_transferencia'">
                                <input type="text" name="payment_reference" placeholder="Folio de transacción"
                                    class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            </template>
                            <button type="submit" :disabled="cart.length === 0"
                                class="w-full inline-flex justify-center items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors">
                                Cobrar $<span x-text="total.toFixed(2)"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function posApp() {
    return {
        search: '',
        cart: [],
        paymentMethod: 'efectivo',
        products: @json($products),
        get filteredProducts() {
            if (!this.search) return this.products;
            const s = this.search.toLowerCase();
            return this.products.filter(p => p.name.toLowerCase().includes(s) || (p.code && p.code.toLowerCase().includes(s)) || (p.barcode && p.barcode.toLowerCase().includes(s)));
        },
        get subtotal() { return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0); },
        get taxTotal() { return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price * (item.tax_percentage / 100)), 0); },
        get total() { return this.subtotal + this.taxTotal; },
        addToCart(product) {
            const existing = this.cart.find(item => item.product_id === product.id);
            if (existing) { existing.quantity++; }
            else {
                this.cart.push({
                    product_id: product.id,
                    type: product.type,
                    description: product.name,
                    quantity: 1,
                    unit_price: parseFloat(product.sale_price),
                    tax_percentage: parseFloat(product.tax_percentage || 0),
                });
            }
        },
        removeFromCart(index) { this.cart.splice(index, 1); },
        prepareForm() { if (this.cart.length === 0) { event.preventDefault(); } }
    }
}
</script>
@endsection

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
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Carrito</h2>
                    <span x-show="cart.length > 0" class="text-xs text-gray-500 dark:text-gray-400" x-text="cart.length + ' artículo(s)'"></span>
                </div>
                <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
                    <template x-if="cart.length === 0">
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Carrito vacío</p>
                    </template>
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg group">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" x-text="item.description"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span x-text="item.quantity"></span> x $<span x-text="parseFloat(item.unit_price).toFixed(2)"></span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                <div class="flex items-center gap-1">
                                    <button @click="updateQuantity(index, -1)" class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs font-bold hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">−</button>
                                    <span class="text-sm font-semibold w-6 text-center" x-text="item.quantity"></span>
                                    <button @click="updateQuantity(index, 1)" class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs font-bold hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">+</button>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 w-20 text-right">$<span x-text="parseFloat(item.quantity * item.unit_price).toFixed(2)"></span></span>
                                <button @click="removeFromCart(index)" class="text-red-600 dark:text-red-400 hover:text-red-500 text-xs opacity-0 group-hover:opacity-100 transition-opacity">✕</button>
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
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                    <button @click="openCheckoutModal(false)" :disabled="cart.length === 0"
                        class="w-full inline-flex justify-center items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors">
                        Cobrar $<span x-text="total.toFixed(2)"></span>
                    </button>
                    <button @click="openCheckoutModal(true)" :disabled="cart.length === 0"
                        class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors">
                        Cobrar con vista previa
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('pos.checkout') }}" id="checkoutForm">
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
        <input type="hidden" name="payment_method" id="formPaymentMethod" value="">
        <input type="hidden" name="amount_received" id="formAmountReceived" value="">
        <input type="hidden" name="cash_amount" id="formCashAmount" value="">
        <input type="hidden" name="card_amount" id="formCardAmount" value="">
        <input type="hidden" name="payment_reference" id="formPaymentReference" value="">
        <input type="hidden" name="preview" id="formPreview" value="">
    </form>

    <div x-show="showCheckoutModal" class="fixed inset-0 z-50 flex items-center justify-center" x-cloak>
        <div x-show="showCheckoutModal"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/80" @click="showCheckoutModal = false"></div>
        <div x-show="showCheckoutModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 sm:max-w-lg w-full mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Confirmar pago</h3>
            <div class="space-y-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 space-y-1">
                    <div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">Subtotal</span><span class="text-gray-900 dark:text-gray-100">$<span x-text="subtotal.toFixed(2)"></span></span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">Impuestos</span><span class="text-gray-900 dark:text-gray-100">$<span x-text="taxTotal.toFixed(2)"></span></span></div>
                    <div class="flex justify-between text-base font-bold border-t border-gray-200 dark:border-gray-600 pt-1"><span class="text-gray-900 dark:text-gray-100">Total a pagar</span><span class="text-indigo-600 dark:text-indigo-400">$<span x-text="total.toFixed(2)"></span></span></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Método de pago</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="method in paymentMethods" :key="method.value">
                            <button @click="paymentMethod = method.value; resetPaymentFields()" type="button"
                                :class="{'bg-indigo-600 text-white ring-2 ring-indigo-500': paymentMethod === method.value, 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600': paymentMethod !== method.value}"
                                class="px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                <span x-text="method.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <template x-if="paymentMethod === 'efectivo'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto recibido</label>
                        <input type="number" x-model="amountReceived" step="0.01" min="0" placeholder="0.00"
                            class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <template x-if="changeAmount > 0">
                            <p class="mt-1 text-sm text-green-600 dark:text-green-400 font-medium">Cambio: $<span x-text="changeAmount.toFixed(2)"></span></p>
                        </template>
                    </div>
                </template>

                <template x-if="paymentMethod === 'tarjeta_transferencia'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Folio de transacción</label>
                        <input type="text" x-model="paymentReference" placeholder="Folio de tarjeta/transferencia"
                            class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Se cobrarán $<span x-text="total.toFixed(2)"></span> con tarjeta.</p>
                    </div>
                </template>

                <template x-if="paymentMethod === 'mixto'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto con tarjeta</label>
                            <input type="number" x-model="cardAmount" step="0.01" min="0" :max="total" placeholder="0.00"
                                class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máximo: $<span x-text="total.toFixed(2)"></span></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto en efectivo</label>
                            <input type="number" x-model="cashAmount" step="0.01" min="0" placeholder="0.00"
                                class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Folio de transacción (tarjeta)</label>
                            <input type="text" x-model="paymentReference" placeholder="Folio de la tarjeta"
                                class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        </div>
                        <template x-if="mixtoChange > 0">
                            <p class="text-sm text-green-600 dark:text-green-400 font-medium">Cambio: $<span x-text="mixtoChange.toFixed(2)"></span></p>
                        </template>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-show="parseFloat(cardAmount || 0) + parseFloat(cashAmount || 0) < total">
                            * Faltan $<span x-text="(total - parseFloat(cardAmount || 0) - parseFloat(cashAmount || 0)).toFixed(2)"></span> para completar el total
                        </p>
                    </div>
                </template>
            </div>
            <div class="mt-6 flex gap-3 justify-end">
                <button @click="showCheckoutModal = false" type="button"
                    class="rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Cancelar
                </button>
                <button @click="submitCheckout()" type="button"
                    class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 transition-colors">
                    Confirmar pago
                </button>
            </div>
        </div>
    </div>

    <div x-show="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center" x-cloak>
        <div x-show="showPreviewModal"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/80" @click="showPreviewModal = false"></div>
        <div x-show="showPreviewModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 sm:max-w-2xl w-full mx-4 p-6 max-h-[85vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Vista previa del ticket</h3>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4 font-mono text-sm leading-relaxed" x-html="previewHtml"></div>
            <div class="flex gap-3 justify-end">
                <button @click="showPreviewModal = false" type="button"
                    class="rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Cerrar
                </button>
                <button @click="printTicket()" type="button"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function posApp() {
    return {
        search: '',
        cart: [],
        paymentMethod: 'efectivo',
        amountReceived: 0,
        cashAmount: 0,
        cardAmount: 0,
        paymentReference: '',
        showCheckoutModal: false,
        showPreviewModal: false,
        previewMode: false,
        previewHtml: '',
        previewSaleId: {{ $previewSaleId ?? 'null' }},
        products: @json($products),
        paymentMethods: [
            { value: 'efectivo', label: 'Efectivo' },
            { value: 'tarjeta_transferencia', label: 'Tarjeta' },
            { value: 'mixto', label: 'Mixto' },
        ],
        get filteredProducts() {
            if (!this.search) return this.products;
            const s = this.search.toLowerCase();
            return this.products.filter(p => p.name.toLowerCase().includes(s) || (p.code && p.code.toLowerCase().includes(s)) || (p.barcode && p.barcode.toLowerCase().includes(s)));
        },
        get subtotal() { return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0); },
        get taxTotal() { return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price * (item.tax_percentage / 100)), 0); },
        get total() { return this.subtotal + this.taxTotal; },
        get changeAmount() { return Math.max(0, parseFloat(this.amountReceived || 0) - this.total); },
        get mixtoChange() {
            const card = parseFloat(this.cardAmount || 0);
            const cash = parseFloat(this.cashAmount || 0);
            const remaining = this.total - card;
            return Math.max(0, cash - remaining);
        },
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
        updateQuantity(index, delta) {
            const newQty = this.cart[index].quantity + delta;
            if (newQty > 0) { this.cart[index].quantity = newQty; }
            else { this.cart.splice(index, 1); }
        },
        removeFromCart(index) { this.cart.splice(index, 1); },
        openCheckoutModal(preview) {
            if (this.cart.length === 0) return;
            this.previewMode = preview;
            this.paymentMethod = 'efectivo';
            this.amountReceived = 0;
            this.cashAmount = 0;
            this.cardAmount = 0;
            this.paymentReference = '';
            this.showCheckoutModal = true;
        },
        resetPaymentFields() {
            this.amountReceived = 0;
            this.cashAmount = 0;
            this.cardAmount = 0;
            this.paymentReference = '';
        },
        submitCheckout() {
            document.getElementById('formPaymentMethod').value = this.paymentMethod;
            document.getElementById('formAmountReceived').value = this.amountReceived;
            document.getElementById('formCashAmount').value = this.cashAmount;
            document.getElementById('formCardAmount').value = this.cardAmount;
            document.getElementById('formPaymentReference').value = this.paymentReference;
            document.getElementById('formPreview').value = this.previewMode ? '1' : '';
            document.getElementById('checkoutForm').submit();
        },
        init() {
            if (this.previewSaleId) {
                this.loadPreview(this.previewSaleId);
            }
        },
        loadPreview(saleId) {
            fetch('/pos/print/' + saleId + '/preview')
                .then(r => r.text())
                .then(html => {
                    this.previewHtml = html;
                    this.showPreviewModal = true;
                });
        },
        printTicket() {
            if (this.previewSaleId) {
                window.open('/pos/print/' + this.previewSaleId, '_blank', 'width=400,height=600');
            }
        }
    }
}
</script>
@endsection

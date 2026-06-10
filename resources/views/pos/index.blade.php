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

    @if($workOrder && $workOrder->quote && $workOrder->quote->status === 'aprobada')
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">Cobro de orden: {{ $workOrder->work_order_number }}</p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ $workOrder->client->name }} — {{ $workOrder->device_brand }} {{ $workOrder->device_model }}</p>
            </div>
        </div>
        <a href="{{ route('work_orders.show', $workOrder) }}" class="text-sm font-medium text-emerald-700 dark:text-emerald-300 hover:text-emerald-500">Ver orden</a>
    </div>
    @endif

    @if($cashRegister)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                <!-- Tabs Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 rounded-t-lg">
                    <nav class="-mb-px flex space-x-6 px-4" aria-label="Tabs">
                        <button @click="activeTab = 'products'" type="button"
                            :class="activeTab === 'products' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap border-b-2 py-4 px-2 text-sm font-semibold transition-colors flex items-center gap-2 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Productos y Servicios
                        </button>
                        <button @click="activeTab = 'quotes'" type="button"
                            :class="activeTab === 'quotes' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap border-b-2 py-4 px-2 text-sm font-semibold transition-colors flex items-center gap-2 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Cotizaciones
                            <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-900/40 px-2.5 py-0.5 text-xs font-bold text-indigo-700 dark:text-indigo-300" x-text="activeQuotes.length"></span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content: Products -->
                <div x-show="activeTab === 'products'">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <input type="text" x-model="search" placeholder="Buscar producto por nombre o código..."
                            class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    </div>
                    <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400" x-text="search ? 'Resultados de búsqueda' : 'Top vendidos'"></span>
                        <span class="text-xs text-gray-400" x-text="filteredProducts.length + ' producto(s)'"></span>
                    </div>
                    <div class="p-4 max-h-[30rem] overflow-y-auto">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="product in filteredProducts" :key="product.id">
                                <button @click="addToCart(product)"
                                    class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors text-left relative flex gap-3 items-start w-full">
                                    <template x-if="product.image_url">
                                        <img :src="'/r2/' + product.image_url" :alt="product.name"
                                            class="w-12 h-12 rounded-md object-cover flex-shrink-0 border border-gray-100 dark:border-gray-600">
                                    </template>
                                    <template x-if="!product.image_url">
                                        <div class="w-12 h-12 rounded-md bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"></path>
                                            </svg>
                                        </div>
                                    </template>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" x-text="product.name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="'$' + parseFloat(product.sale_price).toFixed(2)"></p>
                                        <div class="flex items-center gap-1">
                                            <template x-if="product.reserved_stock > 0">
                                                <p class="text-xs" :class="product.available_stock > 0 ? 'text-amber-500' : 'text-red-500 font-semibold'" x-text="product.available_stock > 0 ? 'Disp: ' + product.available_stock + ' (' + product.reserved_stock + ' apartados)' : 'Apartados: ' + product.reserved_stock"></p>
                                            </template>
                                            <template x-if="product.reserved_stock <= 0">
                                                <p class="text-xs" :class="product.stock > 0 ? 'text-gray-400' : 'text-red-500 font-semibold'" x-text="product.stock > 0 ? 'Stock: ' + product.stock : 'Sin stock'"></p>
                                            </template>
                                        </div>
                                    </div>
                                </button>
                            </template>
                            <template x-if="filteredProducts.length === 0">
                                <p class="col-span-full text-sm text-gray-500 dark:text-gray-400 text-center py-8">No se encontraron productos</p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Quotes -->
                <div x-show="activeTab === 'quotes'" x-cloak>
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <input type="text" x-model="quoteSearch" placeholder="Buscar cotización por folio, cliente, modelo, etc..."
                            class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    </div>
                    <div class="p-4 max-h-[30rem] overflow-y-auto space-y-4">
                        <template x-for="quote in filteredQuotes" :key="quote.id">
                            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-700 pb-3 mb-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 dark:text-gray-100" x-text="'Cotización #' + quote.id"></span>
                                            <template x-if="quote.work_order">
                                                <span class="text-xs text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded font-semibold" x-text="'OT: ' + quote.work_order.work_order_number"></span>
                                            </template>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="quote.work_order ? quote.work_order.device_brand + ' ' + quote.work_order.device_model : ''"></p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full"
                                              :class="{
                                                  'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': quote.status === 'pendiente',
                                                  'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': quote.status === 'enviada',
                                                  'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300': quote.status === 'aprobada'
                                              }"
                                              x-text="quote.status.toUpperCase()"></span>
                                        <span class="font-bold text-gray-900 dark:text-gray-100 text-lg" x-text="'$' + parseFloat(quote.total).toFixed(2)"></span>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" x-text="quote.work_order && quote.work_order.client ? quote.work_order.client.name : 'Mostrador'"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="quote.work_order && quote.work_order.client ? quote.work_order.client.phone : ''"></p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                        <button @click="showQuoteDetails(quote)" type="button" class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-2.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                            Ver Detalle
                                        </button>
                                        
                                        <!-- Autorizar -->
                                        <template x-if="(quote.status === 'pendiente' || quote.status === 'enviada') && canApproveQuotes">
                                            <button @click="approveQuote(quote)" type="button" class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 transition-colors">
                                                Autorizar
                                            </button>
                                        </template>

                                        <!-- Cancelar -->
                                        <button @click="confirmCancelQuote(quote)" type="button" class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-950/20 px-2.5 py-1.5 text-xs font-semibold text-red-700 dark:text-red-400 shadow-sm hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                                            Cancelar
                                        </button>

                                        <!-- Cobrar -->
                                        <template x-if="quote.status === 'aprobada'">
                                            <button @click="chargeQuote(quote)" type="button" class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                                Cobrar
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="filteredQuotes.length === 0">
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No se encontraron cotizaciones activas</p>
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
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg group relative">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-start gap-2 min-w-0 flex-1">
                                    <template x-if="item.product_id">
                                        <img :src="'/r2/' + (products.find(p => p.id === item.product_id)?.image_url || '')"
                                            class="w-10 h-10 rounded object-cover flex-shrink-0 border border-gray-200 dark:border-gray-600"
                                            :class="{'hidden': !products.find(p => p.id === item.product_id)?.image_url}">
                                    </template>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 leading-tight" x-text="item.description"></p>
                                </div>
                                <button @click="removeFromCart(index)" class="text-red-600 dark:text-red-400 hover:text-red-500 text-xs opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0 mt-0.5">✕</button>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center gap-1">
                                    <button @click="updateQuantity(index, -1)" class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-sm font-bold hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">−</button>
                                    <span class="text-sm font-semibold w-7 text-center" x-text="item.quantity"></span>
                                    <button @click="updateQuantity(index, 1)" class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-sm font-bold hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">+</button>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">$<span x-text="parseFloat(item.quantity * item.unit_price).toFixed(2)"></span></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><span x-text="item.quantity"></span> x $<span x-text="parseFloat(item.unit_price).toFixed(2)"></span></p>
                                </div>
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
        <input type="hidden" name="work_order_id" id="formWorkOrderId" value="{{ $workOrder?->id }}">
    </form>

    <div x-show="showCheckoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0" x-cloak>
        <div x-show="showCheckoutModal"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" @click="showCheckoutModal = false"></div>
        <div x-show="showCheckoutModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 sm:max-w-5xl w-full mx-auto p-6 lg:p-8 overflow-y-auto max-h-[95vh]">
            
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Confirmar pago</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Revisa los montos y confirma el pago del carrito</p>
                </div>
                <button @click="showCheckoutModal = false" class="ml-auto text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Panel -->
                <div class="space-y-6">
                    <!-- Step 1 -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
                        <h3 class="flex items-center gap-2 text-lg font-semibold text-indigo-600 dark:text-indigo-400 mb-1">
                            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">1</span>
                            Monto recibido
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 ml-8">Ingresa cuánto recibió del cliente.</p>
                        
                        <div class="space-y-3 ml-8">
                            <!-- Efectivo -->
                            <div class="flex items-center justify-between border border-gray-200 dark:border-gray-600 rounded-lg p-3 hover:border-indigo-300 transition-colors">
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Efectivo</span>
                                </div>
                                <div class="relative w-36">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" x-model="cashAmount" step="0.01" min="0" class="block w-full text-right rounded-md border-0 py-1.5 pl-7 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600" />
                                </div>
                            </div>
                            <!-- Tarjeta -->
                            <div class="flex items-center justify-between border border-gray-200 dark:border-gray-600 rounded-lg p-3 hover:border-indigo-300 transition-colors">
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Tarjeta</span>
                                </div>
                                <div class="relative w-36">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" x-model="cardAmount" step="0.01" min="0" class="block w-full text-right rounded-md border-0 py-1.5 pl-7 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600" />
                                </div>
                            </div>
                            
                            <div x-show="cardAmount > 0" x-collapse>
                                <div class="flex items-center justify-between border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-blue-50 dark:bg-gray-700/50 mt-3">
                                    <span class="font-medium text-gray-700 dark:text-gray-300 text-sm">Ref. de Pago</span>
                                    <input type="text" x-model="paymentReference" placeholder="Folio de tarjeta" class="block w-48 rounded-md border-0 py-1.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600" />
                                </div>
                            </div>

                            <!-- Total Recibido Box -->
                            <div class="bg-indigo-50 dark:bg-indigo-900/30 rounded-lg p-4 flex justify-between items-center mt-4 border border-indigo-100 dark:border-indigo-800">
                                <span class="font-semibold text-indigo-700 dark:text-indigo-300">Total recibido</span>
                                <span class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">$<span x-text="totalReceived.toFixed(2)"></span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
                        <h3 class="flex items-center gap-2 text-lg font-semibold text-indigo-600 dark:text-indigo-400 mb-1">
                            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">2</span>
                            Cambio a regresar
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 ml-8">Este es el cambio que debes regresar al cliente.</p>
                        
                        <div class="ml-8">
                            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-5 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="font-bold text-green-700 dark:text-green-300 text-lg">Cambio a regresar</span>
                                </div>
                                <span class="text-3xl font-black text-green-600 dark:text-green-400">$<span x-text="unifiedChange.toFixed(2)"></span></span>
                            </div>
                            
                            <div class="mt-4 flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-100 dark:border-gray-600">
                                <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Si el cambio es correcto, confirma el pago para finalizar la venta.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel -->
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-6 border border-gray-200 dark:border-gray-700 flex flex-col h-full">
                    <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 border-b border-gray-200 dark:border-gray-600 pb-3">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Resumen del carrito
                    </h3>
                    
                    <div class="flex-1 overflow-y-auto pr-2 max-h-64 mb-4">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-600">
                                <tr>
                                    <th class="py-2 font-medium">Producto</th>
                                    <th class="py-2 text-center font-medium">Cant</th>
                                    <th class="py-2 text-right font-medium">Precio unit.</th>
                                    <th class="py-2 text-right font-medium">Importe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                <template x-for="(item, index) in cart" :key="index">
                                    <tr>
                                        <td class="py-3">
                                            <p class="font-medium text-gray-900 dark:text-gray-100 truncate w-32 sm:w-48" x-text="item.description"></p>
                                        </td>
                                        <td class="py-3 text-center text-gray-700 dark:text-gray-300" x-text="item.quantity"></td>
                                        <td class="py-3 text-right text-gray-700 dark:text-gray-300">$<span x-text="parseFloat(item.unit_price).toFixed(2)"></span></td>
                                        <td class="py-3 text-right font-medium text-gray-900 dark:text-gray-100">$<span x-text="parseFloat(item.quantity * item.unit_price).toFixed(2)"></span></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-600 pt-4 space-y-2 mt-auto">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium">$<span x-text="subtotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Impuestos (IVA)</span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium">$<span x-text="taxTotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between text-xl font-bold border-t border-gray-200 dark:border-gray-600 pt-3 mt-3">
                            <span class="text-gray-900 dark:text-gray-100">TOTAL GENERAL</span>
                            <span class="text-gray-900 dark:text-gray-100">$<span x-text="total.toFixed(2)"></span></span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <button @click="submitCheckout(false)" type="button" :disabled="totalReceived < total"
                            class="w-full flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-3.5 text-base font-bold text-white shadow-sm hover:bg-green-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Confirmar pago
                        </button>
                        <button @click="submitCheckout(true)" type="button" :disabled="totalReceived < total"
                            class="w-full flex items-center justify-center gap-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Cobrar con vista previa
                        </button>
                        <p class="text-xs text-center text-gray-500 dark:text-gray-400 flex items-center justify-center gap-1.5 mt-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Al confirmar, se finalizará la venta y se generará el comprobante.
                        </p>
                    </div>
                </div>
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

    <div x-show="showStockError" class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showStockError = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-md mx-auto p-6 text-center">
            <div class="mx-auto w-14 h-14 flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Stock insuficiente</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6" x-text="stockError"></p>
            <button @click="showStockError = false" class="rounded-md bg-red-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors">
                Entendido
            </button>
        </div>
    </div>

    <!-- Quote Detail Modal -->
    <div x-show="showQuoteDetailModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showQuoteDetailModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-lg mx-auto p-6 max-h-[85vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                    Cotización #<span x-text="selectedQuote?.id"></span>
                </h3>
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full"
                      :class="{
                          'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': selectedQuote?.status === 'pendiente',
                          'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': selectedQuote?.status === 'enviada',
                          'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300': selectedQuote?.status === 'aprobada'
                      }"
                      x-text="selectedQuote?.status?.toUpperCase()"></span>
            </div>
            <template x-if="selectedQuote?.work_order">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg space-y-1">
                    <p><span class="font-medium">OT:</span> <span x-text="selectedQuote.work_order.work_order_number"></span></p>
                    <p><span class="font-medium">Cliente:</span> <span x-text="selectedQuote.work_order.client?.name || 'Mostrador'"></span></p>
                    <p><span class="font-medium">Tel:</span> <span x-text="selectedQuote.work_order.client?.phone || '-'"></span></p>
                    <p><span class="font-medium">Equipo:</span> <span x-text="(selectedQuote.work_order.device_brand || '') + ' ' + (selectedQuote.work_order.device_model || '')"></span></p>
                </div>
            </template>
            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-600">
                        <th class="text-left py-2 font-medium text-gray-600 dark:text-gray-400">Producto</th>
                        <th class="text-center py-2 font-medium text-gray-600 dark:text-gray-400">Cant</th>
                        <th class="text-right py-2 font-medium text-gray-600 dark:text-gray-400">Precio</th>
                        <th class="text-right py-2 font-medium text-gray-600 dark:text-gray-400">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="item in (selectedQuote?.quote_items || [])" :key="item.id">
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-gray-100" x-text="item.description"></td>
                            <td class="py-2 text-center text-gray-700 dark:text-gray-300" x-text="item.quantity"></td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">$<span x-text="parseFloat(item.unit_price).toFixed(2)"></span></td>
                            <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100">$<span x-text="parseFloat(item.quantity * item.unit_price).toFixed(2)"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-600 pt-3">
                <span>Total</span>
                <span>$<span x-text="parseFloat(selectedQuote?.total || 0).toFixed(2)"></span></span>
            </div>
            <div class="flex justify-end mt-4">
                <button @click="showQuoteDetailModal = false" class="rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Cancel Quote Modal -->
    <div x-show="showCancelModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showCancelModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-md mx-auto p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Cancelar Cotización</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Ingresa el motivo de cancelación. Esta acción liberará el inventario apartado.</p>
            <textarea x-model="cancelReason" rows="3"
                      class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-red-600 dark:bg-gray-700 sm:text-sm sm:leading-6 mb-4"
                      placeholder="Motivo de cancelación..."></textarea>
            <div class="flex justify-end gap-3">
                <button @click="showCancelModal = false" class="rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Volver
                </button>
                <button @click="cancelQuote()" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors">
                    Cancelar cotización
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function posApp() {
    return {
        search: '',
        activeTab: 'products',
        quoteSearch: '',
        activeQuotes: @json($activeQuotes),
        canApproveQuotes: @can('quotes.approve') true @else false @endcan,
        selectedQuote: null,
        showQuoteDetailModal: false,
        showCancelModal: false,
        cancelQuoteTarget: null,
        cancelReason: '',
        get filteredQuotes() {
            if (!this.quoteSearch) return this.activeQuotes;
            const s = this.quoteSearch.toLowerCase();
            return this.activeQuotes.filter(q => {
                const wo = q.work_order || {};
                const num = wo.work_order_number || '';
                const client = wo.client || {};
                const model = (wo.device_brand || '') + ' ' + (wo.device_model || '');
                return num.toLowerCase().includes(s)
                    || (client.name || '').toLowerCase().includes(s)
                    || (client.phone || '').toLowerCase().includes(s)
                    || model.toLowerCase().includes(s);
            });
        },
        cart: [],
        paymentMethod: 'efectivo',
        amountReceived: 0,
        cashAmount: 0,
        cardAmount: 0,
        paymentReference: '',
        showCheckoutModal: false,
        showPreviewModal: false,
        showStockError: false,
        stockError: '',
        previewMode: false,
        previewHtml: '',
        previewSaleId: {{ $previewSaleId ?? 'null' }},
        topProducts: @json($topProducts),
        products: @json($products),
        cashRegisterId: {{ $cashRegister?->id ?? 'null' }},
        workOrder: @json($workOrder),
        quoteItems: @json($quoteItems),
        paymentMethods: [
            { value: 'efectivo', label: 'Efectivo' },
            { value: 'tarjeta_transferencia', label: 'Tarjeta' },
            { value: 'mixto', label: 'Mixto' },
        ],
        get filteredProducts() {
            if (!this.search) return this.topProducts;
            const s = this.search.toLowerCase();
            return this.products.filter(p => p.name.toLowerCase().includes(s) || (p.code && p.code.toLowerCase().includes(s)) || (p.barcode && p.barcode.toLowerCase().includes(s)));
        },
        get subtotal() { return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0); },
        get taxTotal() { return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price * (item.tax_percentage / 100)), 0); },
        get total() { return this.subtotal + this.taxTotal; },
        get changeAmount() { return Math.max(0, parseFloat(this.amountReceived || 0) - this.total); },
        get totalReceived() { return parseFloat(this.cashAmount || 0) + parseFloat(this.cardAmount || 0); },
        get unifiedChange() { return Math.max(0, this.totalReceived - this.total); },
        get derivedPaymentMethod() {
            if (parseFloat(this.cardAmount || 0) > 0 && parseFloat(this.cashAmount || 0) > 0) return 'mixto';
            if (parseFloat(this.cardAmount || 0) > 0) return 'tarjeta_transferencia';
            return 'efectivo';
        },
        getStock(productId) {
            const p = this.products.find(p => p.id === productId);
            return p ? p.available_stock : 0;
        },
        getCartQty(productId) {
            return this.cart.filter(i => i.product_id === productId).reduce((sum, i) => sum + i.quantity, 0);
        },
        addToCart(product) {
            const existing = this.cart.find(item => item.product_id === product.id);
            const currentQty = existing ? existing.quantity : 0;
            if (product.type === 'producto' && currentQty >= product.available_stock) {
                this.stockError = 'Stock insuficiente para "' + product.name + '". Disponible: ' + product.available_stock;
                this.showStockError = true;
                return;
            }
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
            const item = this.cart[index];
            const newQty = item.quantity + delta;
            if (newQty > 0) {
                if (delta > 0 && item.product_id) {
                    const product = this.products.find(p => p.id === item.product_id);
                    if (product && product.type === 'producto' && newQty > product.available_stock) {
                        this.stockError = 'Stock insuficiente para "' + item.description + '". Disponible: ' + product.available_stock;
                        this.showStockError = true;
                        return;
                    }
                }
                this.cart[index].quantity = newQty;
            } else {
                this.cart.splice(index, 1);
            }
        },
        submitCheckout(preview) {
            if (this.cart.length === 0) return;
            for (const item of this.cart) {
                if (item.product_id) {
                    const product = this.products.find(p => p.id === item.product_id);
                    if (product && product.type === 'producto' && item.quantity > product.available_stock) {
                        this.stockError = 'Stock insuficiente para "' + item.description + '". Disponible: ' + product.available_stock + ', solicitado: ' + item.quantity;
                        this.showStockError = true;
                        return;
                    }
                }
            }
            if (this.totalReceived < this.total) {
                alert('El monto recibido es menor al total a pagar.');
                return;
            }
            if ((this.derivedPaymentMethod === 'tarjeta_transferencia' || this.derivedPaymentMethod === 'mixto') && !this.paymentReference) {
                alert('Debe ingresar el folio o referencia del pago con tarjeta.');
                return;
            }
            if (preview !== undefined) this.previewMode = preview;
            
            document.getElementById('formPaymentMethod').value = this.derivedPaymentMethod;
            document.getElementById('formAmountReceived').value = this.cashAmount;
            document.getElementById('formCashAmount').value = this.cashAmount;
            document.getElementById('formCardAmount').value = this.cardAmount;
            document.getElementById('formPaymentReference').value = this.paymentReference;
            document.getElementById('formPreview').value = this.previewMode ? '1' : '';
            document.getElementById('checkoutForm').submit();
        },
        removeFromCart(index) { this.cart.splice(index, 1); },
        openCheckoutModal(preview) {
            if (this.cart.length === 0) return;
            this.previewMode = preview;
            this.cashAmount = this.total;
            this.cardAmount = 0;
            this.paymentReference = '';
            this.showCheckoutModal = true;
        },
        showQuoteDetails(quote) {
            this.selectedQuote = quote;
            this.showQuoteDetailModal = true;
        },
        approveQuote(quote) {
            if (!confirm('¿Autorizar esta cotización? Se reservará el inventario.')) return;
            fetch('/quotes/' + quote.id + '/approve', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    quote.status = 'aprobada';
                    alert(data.message);
                } else {
                    alert(data.message || 'Error al autorizar la cotización.');
                }
            })
            .catch(() => alert('Error de conexión.'));
        },
        confirmCancelQuote(quote) {
            this.cancelQuoteTarget = quote;
            this.cancelReason = '';
            this.showCancelModal = true;
        },
        cancelQuote() {
            if (!this.cancelReason.trim()) {
                alert('Debes ingresar un motivo de cancelación.');
                return;
            }
            fetch('/quotes/' + this.cancelQuoteTarget.id + '/reject', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason: this.cancelReason })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const idx = this.activeQuotes.indexOf(this.cancelQuoteTarget);
                    if (idx !== -1) this.activeQuotes.splice(idx, 1);
                    this.showCancelModal = false;
                    this.cancelQuoteTarget = null;
                    this.cancelReason = '';
                    alert(data.message);
                } else {
                    alert(data.message || 'Error al cancelar la cotización.');
                }
            })
            .catch(() => alert('Error de conexión.'));
        },
        chargeQuote(quote) {
            // Load quote items into cart
            this.cart = [];
            (quote.quote_items || []).forEach(qi => {
                this.cart.push({
                    product_id: qi.product_id,
                    type: qi.type,
                    description: qi.description,
                    quantity: qi.quantity,
                    unit_price: parseFloat(qi.unit_price),
                    tax_percentage: parseFloat(qi.tax_percentage || 0),
                });
            });
            // Set work_order_id on form
            if (quote.work_order && quote.work_order.id) {
                document.getElementById('formWorkOrderId').value = quote.work_order.id;
            }
            this.$nextTick(() => this.openCheckoutModal(false));
        },
        init() {
            // Pre-populate cart from quote items (cobro_orden)
            if (this.workOrder && this.quoteItems && this.quoteItems.length > 0 && this.cashRegisterId) {
                this.quoteItems.forEach(qi => {
                    this.cart.push({
                        product_id: qi.product_id,
                        type: qi.type,
                        description: qi.description,
                        quantity: qi.quantity,
                        unit_price: parseFloat(qi.unit_price),
                        tax_percentage: parseFloat(qi.tax_percentage || 0),
                    });
                });
                // Auto-open checkout modal
                this.$nextTick(() => this.openCheckoutModal(false));
            }
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

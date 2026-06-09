@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Nueva Devolución</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Busque una venta por folio para iniciar la devolución.</p>
        </div>
        <a href="{{ route('returns.index') }}" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            ← Volver
        </a>
    </div>

    <div x-data="returnForm()" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Folio de venta (número o referencia de pago)
            </label>
            <div class="flex gap-2">
                <input type="text" x-model="folio" @keyup.enter="searchSale"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    placeholder="Ej: 42 o TXN-12345">
                <button @click="searchSale" :disabled="loading"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 transition-colors">
                    <span x-show="!loading">Buscar</span>
                    <span x-show="loading">Buscando...</span>
                </button>
            </div>
            <p x-show="error" x-text="error" class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
        </div>

        <div x-show="saleFound" x-cloak class="space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Venta #<span x-text="sale.id"></span></h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div><span class="text-gray-500 dark:text-gray-400">Fecha:</span> <span class="font-medium text-gray-900 dark:text-gray-100" x-text="sale.created_at"></span></div>
                    <div><span class="text-gray-500 dark:text-gray-400">Cliente:</span> <span class="font-medium text-gray-900 dark:text-gray-100" x-text="sale.client_name"></span></div>
                    <div><span class="text-gray-500 dark:text-gray-400">Pago:</span> <span class="font-medium text-gray-900 dark:text-gray-100" x-text="sale.payment_method"></span></div>
                    <div><span class="text-gray-500 dark:text-gray-400">Total:</span> <span class="font-medium text-gray-900 dark:text-gray-100">$<span x-text="parseFloat(sale.total).toFixed(2)"></span></span></div>
                </div>
                <div x-show="hasReturn" class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-md text-sm text-yellow-700 dark:text-yellow-400">
                    Esta venta ya tiene una devolución registrada. Puede agregar otra devolución para artículos no devueltos anteriormente.
                </div>
            </div>

            <form @submit.prevent="submitReturn">

                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                    <div class="p-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Seleccione los artículos a devolver</h2>
                    </div>

                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="p-4" x-show="item.available_qty > 0">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" x-model="item.selected" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="item.description"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Vendido: <span x-text="item.quantity"></span> |
                                        Devuelto: <span x-text="item.returned_qty"></span> |
                                        Disponible: <span x-text="item.available_qty"></span> |
                                        Precio: $<span x-text="parseFloat(item.unit_price).toFixed(2)"></span>
                                    </p>
                                </div>
                            </label>

                            <div x-show="item.selected" x-cloak class="mt-3 ml-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad a devolver</label>
                                    <input type="number" x-model="item.qty"
                                        :max="item.available_qty" min="1"
                                        @input="item.refund = Math.min(item.qty * item.unit_price, item.refund)"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Monto a reembolsar</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-sm text-gray-500">$</span>
                                        <input type="number" step="0.01" x-model="item.refund"
                                            :max="item.qty * item.unit_price" min="0"
                                            class="block w-full pl-7 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Máx: $<span x-text="(item.qty * item.unit_price).toFixed(2)"></span></p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Destino</label>
                                    <select x-model="item.restock"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="1">Regresar a inventario</option>
                                        <option value="0">Marcar como merma</option>
                                    </select>
                                </div>

                                <div x-show="!item.restock">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo de merma</label>
                                    <select x-model="item.waste_reason"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">Seleccione...</option>
                                        <option value="mal_aspecto">Mal aspecto (golpeado, rayado)</option>
                                        <option value="mal_funcionamiento">Mal funcionamiento</option>
                                        <option value="defecto_fabrica">Defecto de fábrica</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="selectedCount === 0 && saleFound" class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                        No hay artículos disponibles para devolución en esta venta.
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo general de la devolución</label>
                        <textarea x-model="reason" rows="2"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="Ej: El cliente cambió de opinión, producto defectuoso, etc."></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total a reembolsar:</span>
                            <span class="text-lg font-bold text-red-600 dark:text-red-400 ml-2">$<span x-text="totalRefund.toFixed(2)"></span></span>
                        </div>
                        <button type="submit" :disabled="selectedCount === 0 || loading"
                            class="inline-flex items-center rounded-md bg-red-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 disabled:opacity-50 transition-colors">
                            Registrar Devolución
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function returnForm() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    return {
        folio: '',
        loading: false,
        error: '',
        saleFound: false,
        hasReturn: false,
        sale: {},
        items: [],
        reason: '',
        get selectedCount() {
            return this.items.filter(i => i.selected).length;
        },
        get totalRefund() {
            return this.items
                .filter(i => i.selected)
                .reduce((sum, i) => sum + (parseFloat(i.refund) || 0), 0);
        },
        searchSale() {
            if (!this.folio.trim()) return;
            this.loading = true;
            this.error = '';
            this.saleFound = false;
            fetch('{{ route('returns.search_sale') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ folio: this.folio })
            })
            .then(r => r.json())
            .then(data => {
                if (data.found) {
                    this.sale = data.sale;
                    this.hasReturn = data.has_return;
                    this.items = data.items.map(item => ({
                        ...item,
                        selected: false,
                        qty: Math.min(1, item.available_qty),
                        refund: Math.min(item.unit_price, item.unit_price * item.available_qty),
                        restock: '1',
                        waste_reason: '',
                    }));
                    this.saleFound = true;
                } else {
                    this.error = data.message;
                }
            })
            .catch(() => {
                this.error = 'Error al buscar la venta. Intente de nuevo.';
            })
            .finally(() => {
                this.loading = false;
            });
        },
        submitReturn() {
            if (this.selectedCount === 0) return;
            this.loading = true;

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('sale_id', this.sale.id);
            formData.append('reason', this.reason);

            let idx = 0;
            this.items.forEach(item => {
                if (!item.selected) return;
                const p = `items[${idx}]`;
                formData.append(`${p}[sale_item_id]`, item.id);
                formData.append(`${p}[quantity]`, item.qty);
                formData.append(`${p}[refund_amount]`, item.refund);
                formData.append(`${p}[restock]`, item.restock);
                if (item.restock === '0') {
                    formData.append(`${p}[waste_reason]`, item.waste_reason || 'otro');
                }
                idx++;
            });

            fetch('{{ route('returns.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(r => {
                if (r.redirected) {
                    window.location.href = r.url;
                } else if (r.ok) {
                    window.location.href = '{{ route('returns.index') }}';
                } else {
                    r.json().then(data => {
                        this.error = data.message || Object.values(data.errors || {}).flat().join(', ') || 'Error al registrar la devolución.';
                    }).catch(() => {
                        this.error = 'Error al registrar la devolución.';
                    });
                }
            })
            .catch(() => {
                this.error = 'Error al registrar la devolución.';
            })
            .finally(() => {
                this.loading = false;
            });
        }
    };
}
</script>
@endpush

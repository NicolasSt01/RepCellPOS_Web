@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Impuestos y Formato</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configuración de impuestos, formato de impresión y numeración de órdenes</p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <form method="POST" action="{{ route('settings.taxes.update') }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Impuestos</h3>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="hidden" name="tax_enabled" value="0">
                        <input type="checkbox" name="tax_enabled" id="tax_enabled" value="1" {{ old('tax_enabled', $tenant->tax_enabled) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                        <label for="tax_enabled" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Aplicar impuestos en ventas y cotizaciones</label>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="tax_percentage" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Porcentaje de impuesto por defecto (%)</label>
                            <input type="number" name="tax_percentage" id="tax_percentage" value="{{ old('tax_percentage', $tenant->tax_percentage) }}" step="0.01" min="0" max="100"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        </div>

                        <div>
                            <label for="tax_mode" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Modo de cálculo</label>
                            <select name="tax_mode" id="tax_mode"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                                <option value="per_item" {{ old('tax_mode', $tenant->tax_mode) === 'per_item' ? 'selected' : '' }}>Por producto/servicio individual</option>
                                <option value="on_total" {{ old('tax_mode', $tenant->tax_mode) === 'on_total' ? 'selected' : '' }}>Sobre el total de la venta</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Formato de Impresión</h3>
                <div>
                    <label for="print_format" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Formato preferido</label>
                    <select name="print_format" id="print_format"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="ticket_58mm" {{ old('print_format', $tenant->print_format) === 'ticket_58mm' ? 'selected' : '' }}>Ticket térmico 58mm</option>
                        <option value="ticket_80mm" {{ old('print_format', $tenant->print_format) === 'ticket_80mm' ? 'selected' : '' }}>Ticket térmico 80mm</option>
                        <option value="a4" {{ old('print_format', $tenant->print_format) === 'a4' ? 'selected' : '' }}>Hoja A4</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Numeración de Órdenes</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="work_order_prefix" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Prefijo</label>
                        <input type="text" name="work_order_prefix" id="work_order_prefix" value="{{ old('work_order_prefix', $tenant->work_order_prefix) }}"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <p class="mt-1 text-xs text-gray-500">Ej: OT-, REP-, ORD-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Siguiente número</label>
                        <input type="text" value="{{ $tenant->work_order_prefix }}{{ str_pad($tenant->work_order_sequence + 1, 5, '0', STR_PAD_LEFT) }}" disabled
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-500 dark:text-gray-400 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 bg-gray-50 dark:bg-gray-600 sm:text-sm sm:leading-6">
                        <p class="mt-1 text-xs text-gray-500">Se incrementa automáticamente con cada orden</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    Guardar configuración
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

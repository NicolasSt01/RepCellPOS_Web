@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Nuevo Producto</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <form method="POST" action="{{ route('products.store') }}" class="p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Tipo <span class="text-red-500">*</span></label>
                    <select name="type" id="type" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="producto" {{ old('type') === 'producto' ? 'selected' : '' }}>Producto</option>
                        <option value="servicio" {{ old('type') === 'servicio' ? 'selected' : '' }}>Servicio</option>
                    </select>
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Código / SKU</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label for="barcode" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Código de Barras</label>
                    <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Categoría</label>
                    <select name="category_id" id="category_id"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="">Sin categoría</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="part_number" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Número de Parte</label>
                    <input type="text" name="part_number" id="part_number" value="{{ old('part_number') }}"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label for="purchase_price" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Precio de Compra <span class="text-red-500">*</span></label>
                    <input type="number" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', '0.00') }}" step="0.01" min="0" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Precio de Venta <span class="text-red-500">*</span></label>
                    <input type="number" name="sale_price" id="sale_price" value="{{ old('sale_price', '0.00') }}" step="0.01" min="0" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Stock Inicial</label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', '0') }}" min="0"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label for="min_stock" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Stock Mínimo</label>
                    <input type="number" name="min_stock" id="min_stock" value="{{ old('min_stock', '0') }}" min="0"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label for="compatible_brand" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Marca Compatible</label>
                    <input type="text" name="compatible_brand" id="compatible_brand" value="{{ old('compatible_brand') }}" placeholder="Ej: Apple, Samsung"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label for="compatible_model" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Modelo Compatible</label>
                    <input type="text" name="compatible_model" id="compatible_model" value="{{ old('compatible_model') }}" placeholder="Ej: iPhone 13"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="flex items-center gap-4">
                    <div class="flex items-center">
                        <input type="hidden" name="has_tax" value="0">
                        <input type="checkbox" name="has_tax" id="has_tax" value="1" {{ old('has_tax', true) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                        <label for="has_tax" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Maneja impuesto</label>
                    </div>
                </div>
                <div>
                    <label for="tax_percentage" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Porcentaje de Impuesto (%)</label>
                    <input type="number" name="tax_percentage" id="tax_percentage" value="{{ old('tax_percentage', '16.00') }}" step="0.01" min="0" max="100"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Descripción</label>
                <textarea name="description" id="description" rows="3"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="image" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Imagen del Producto</label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">JPG, PNG, WebP o GIF. Máximo 5MB.</p>
                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp,image/gif"
                    class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:bg-gray-700">
                @error('image') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</a>
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Guardar producto</button>
            </div>
        </form>
    </div>
</div>
@endsection

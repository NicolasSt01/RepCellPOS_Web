@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="productForm()">
    <div class="md:flex md:gap-8">
        
        <!-- Left Sidebar / Step Indicators -->
        <div class="hidden md:block w-64 shrink-0">
            <div class="sticky top-24 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <!-- Box icon representing inventory -->
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">Nuevo<br>Artículo</h2>
                    </div>
                </div>

                <nav class="space-y-4">
                    <template x-for="(s, index) in steps" :key="index">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm border-2 transition-colors"
                                :class="{
                                    'bg-indigo-600 border-indigo-600 text-white': step === index + 1,
                                    'border-indigo-600 text-indigo-600 dark:text-indigo-400': step > index + 1,
                                    'border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400': step < index + 1
                                }">
                                <span x-show="step <= index + 1" x-text="index + 1"></span>
                                <svg x-show="step > index + 1" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium transition-colors"
                                :class="{
                                    'text-indigo-600 dark:text-indigo-400': step === index + 1,
                                    'text-gray-900 dark:text-gray-100': step > index + 1,
                                    'text-gray-500 dark:text-gray-400': step < index + 1
                                }" x-text="s"></span>
                        </div>
                    </template>
                </nav>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-xl">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400" x-text="'Paso ' + step + ' de ' + totalSteps"></span>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white" x-text="steps[step - 1]"></h1>
                </div>
                <a href="{{ route('products.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    Cancelar
                </a>
            </div>

            <!-- Top Progress Bar -->
            <div class="px-6 pt-6">
                <div class="relative">
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-100 dark:bg-indigo-900/30">
                        <div :style="`width: ${(step / totalSteps) * 100}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-600 transition-all duration-300"></div>
                    </div>
                </div>
            </div>

            <form id="productForm" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <!-- STEP 1: TIPO Y NOMBRE -->
                <div x-show="step === 1" x-collapse class="space-y-6">
                    
                    <!-- Selección del Tipo (Producto vs Servicio) -->
                    <div>
                        <label class="block text-base font-bold text-gray-900 dark:text-gray-100 mb-3">¿Qué tipo de artículo vas a registrar? <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            <!-- Opción Producto -->
                            <div @click="type = 'producto'" 
                                 class="cursor-pointer p-5 rounded-xl border-2 transition-all flex flex-col justify-between"
                                 :class="type === 'producto' ? 'border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20 ring-2 ring-indigo-600/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                                        <!-- Box Icon -->
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </div>
                                    <div x-show="type === 'producto'" class="text-indigo-600 dark:text-indigo-400">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">Un Producto Físico</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Cosas que se pueden tocar, guardar en bodega y vender. Ej: Pantallas, baterías, fundas, accesorios, cargadores.</p>
                                </div>
                            </div>

                            <!-- Opción Servicio -->
                            <div @click="type = 'servicio'" 
                                 class="cursor-pointer p-5 rounded-xl border-2 transition-all flex flex-col justify-between"
                                 :class="type === 'servicio' ? 'border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20 ring-2 ring-indigo-600/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                                        <!-- Wrench/Screwdriver icon -->
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                                        </svg>
                                    </div>
                                    <div x-show="type === 'servicio'" class="text-indigo-600 dark:text-indigo-400">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">Un Servicio / Trabajo</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Actividades o mano de obra, sin stock físico. Ej: Mano de obra de reparación, mantenimiento, limpieza, desbloqueo.</p>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="type" :value="type">
                    </div>

                    <!-- Nombre -->
                    <div class="space-y-1">
                        <label for="name" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                            ¿Cómo se llama el producto o servicio? <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" x-model="name" required
                               placeholder="Ej: Pantalla Original iPhone 11 o Limpieza General"
                               class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Usa un nombre sencillo que los clientes reconozcan al ver su ticket de compra.</p>
                    </div>

                    <!-- Categoría -->
                    <div class="space-y-1">
                        <label for="category_id" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Categoría (¿A qué grupo pertenece?)
                        </label>
                        <select id="category_id" name="category_id" x-model="category_id"
                                class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <option value="">Sin categoría / General</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Selecciona un grupo para ordenar tus reportes e inventarios.</p>
                    </div>
                </div>

                <!-- STEP 2: PRECIOS Y GANANCIA -->
                <div x-show="step === 2" x-collapse class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        
                        <!-- Precio de Compra -->
                        <div class="space-y-1">
                            <label for="purchase_price" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Precio de Compra <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                                </div>
                                <input type="number" id="purchase_price" name="purchase_price" x-model.number="purchase_price" step="0.01" min="0" required
                                       class="block w-full rounded-md border-0 py-2 pl-7 pr-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            </div>
                            @error('purchase_price') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">¿Cuánto te costó comprarlo a ti? Pon 0 si es mano de obra o no tiene costo de compra.</p>
                        </div>

                        <!-- Precio de Venta -->
                        <div class="space-y-1">
                            <label for="sale_price" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Precio de Venta <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                                </div>
                                <input type="number" id="sale_price" name="sale_price" x-model.number="sale_price" step="0.01" min="0" required
                                       class="block w-full rounded-md border-0 py-2 pl-7 pr-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            </div>
                            @error('sale_price') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">¿A cuánto se lo vas a vender al público?</p>
                        </div>
                    </div>

                    <!-- Calculadora de Ganancia Interactiva -->
                    <div class="rounded-xl p-5 bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-3-3V18m-3-3V18M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                            </svg>
                            Calculadora de Ganancia Estimada
                        </h4>
                        
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Tu ganancia neta es:</span>
                                <span class="text-3xl font-black transition-colors"
                                      :class="sale_price < purchase_price ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'"
                                      x-text="'$' + profit.toFixed(2)"></span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Margen de Ganancia:</span>
                                <span class="text-3xl font-black transition-colors"
                                      :class="sale_price < purchase_price ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'"
                                      x-text="profitPercentage.toFixed(0) + '%'"></span>
                            </div>
                        </div>

                        <!-- Consejos interactivos -->
                        <div class="mt-4 border-t border-gray-200 dark:border-gray-700/60 pt-3">
                            <template x-if="sale_price > 0 && purchase_price > 0 && sale_price < purchase_price">
                                <div class="flex items-center gap-2 text-red-600 dark:text-red-400 font-semibold p-2 bg-red-50 dark:bg-red-950/20 rounded-lg text-sm">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <span>¡Alerta! Estás vendiendo más barato de lo que te costó. Perderás dinero.</span>
                                </div>
                            </template>
                            <template x-if="sale_price > 0 && profitPercentage > 50">
                                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-semibold p-2 bg-emerald-50 dark:bg-emerald-950/20 rounded-lg text-sm">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>🟢 ¡Muy buen negocio! Tienes un excelente margen de ganancia en este artículo.</span>
                                </div>
                            </template>
                            <template x-if="sale_price > 0 && profitPercentage >= 10 && profitPercentage <= 50">
                                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-semibold p-2 bg-indigo-50 dark:bg-indigo-950/20 rounded-lg text-sm">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 11.517 1.282l-.041.02a.75.75 0 01-.517-1.282z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a6 6 0 100-12 6 6 0 000 12z" />
                                    </svg>
                                    <span>🟡 Tu margen de ganancia es moderado y saludable.</span>
                                </div>
                            </template>
                            <template x-if="sale_price > 0 && profitPercentage > 0 && profitPercentage < 10">
                                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-semibold p-2 bg-amber-50 dark:bg-amber-950/20 rounded-lg text-sm">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
                                    </svg>
                                    <span>⚠️ Tu ganancia es muy baja (menos del 10%). Te sugerimos subir el precio de venta si es posible.</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Impuestos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                        <div class="flex items-center py-2">
                            <div class="flex items-center">
                                <input type="hidden" name="has_tax" value="0">
                                <input type="checkbox" name="has_tax" id="has_tax" value="1" x-model="has_tax"
                                       class="h-5 w-5 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                                <label for="has_tax" class="ml-2.5 block text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    Maneja impuesto (IVA, etc.)
                                </label>
                            </div>
                        </div>
                        <div class="space-y-1" x-show="has_tax" x-collapse>
                            <label for="tax_percentage" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Porcentaje de Impuesto (%)
                            </label>
                            <input type="number" id="tax_percentage" name="tax_percentage" x-model.number="tax_percentage" step="0.01" min="0" max="100"
                                   class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Escribe el porcentaje de impuesto. Por defecto en México es 16.00%</p>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: INVENTARIO Y COMPATIBILIDAD (Only for 'producto') -->
                <div x-show="step === 3 && type === 'producto'" x-collapse class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        
                        <!-- Stock Inicial -->
                        <div class="space-y-1">
                            <label for="stock" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Cantidad Inicial en Bodega (Existencias actuales) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="stock" name="stock" x-model.number="stock" min="0" required
                                   class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            @error('stock') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">¿Cuántos tienes guardados hoy físicamente para vender?</p>
                        </div>

                        <!-- Stock Mínimo -->
                        <div class="space-y-1">
                            <label for="min_stock" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Stock Mínimo (Alerta de recompra)
                            </label>
                            <input type="number" id="min_stock" name="min_stock" x-model.number="min_stock" min="0"
                                   class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            @error('min_stock') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">El sistema te avisará en rojo cuando tus existencias sean menores o iguales a este número para que compres más.</p>
                        </div>
                    </div>

                    <!-- Compatibilidad -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                        
                        <!-- Marca compatible -->
                        <div class="space-y-1">
                            <label for="compatible_brand" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Marca de celular compatible
                            </label>
                            <input type="text" id="compatible_brand" name="compatible_brand" x-model="compatible_brand" placeholder="Ej: Apple, Samsung, Xiaomi"
                                   class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">¿Para qué marca de celular sirve este repuesto? (Opcional)</p>
                        </div>

                        <!-- Modelo compatible -->
                        <div class="space-y-1">
                            <label for="compatible_model" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Modelo de celular compatible
                            </label>
                            <input type="text" id="compatible_model" name="compatible_model" x-model="compatible_model" placeholder="Ej: iPhone 11 Pro, Galaxy S21"
                                   class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">¿A qué modelos específicos de celular le queda este repuesto?</p>
                        </div>
                    </div>

                    <!-- Códigos -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
                        
                        <!-- Código SKU -->
                        <div class="space-y-1">
                            <label for="code" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Código SKU / Interno
                            </label>
                            <input type="text" id="code" name="code" x-model="code" placeholder="Ej: PANT-IPH11"
                                   class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Código de inventario corto de tu tienda (opcional).</p>
                        </div>

                        <!-- Número de parte -->
                        <div class="space-y-1">
                            <label for="part_number" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Número de Parte del Fabricante
                            </label>
                            <input type="text" id="part_number" name="part_number" x-model="part_number" placeholder="Ej: 821-02201"
                                   class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Número impreso en la refacción (opcional).</p>
                        </div>

                        <!-- Código de barras -->
                        <div class="space-y-1">
                            <label for="barcode" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Código de Barras
                            </label>
                            <div class="flex gap-2">
                                <div class="relative flex-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v14.25c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 19.125V4.875zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v14.25c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 19.125V4.875z" />
                                        </svg>
                                    </div>
                                    <input type="text" id="barcode" name="barcode" x-model="barcode" placeholder="Pasa el escáner aquí"
                                           class="block w-full rounded-md border-0 py-2 pl-10 pr-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                                </div>
                                <button type="button" @click="generateBarcode()"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 transition-colors whitespace-nowrap">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" /></svg>
                                    Generar
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Escanea con tu lector o genera un código interno automático.</p>
                            <input type="hidden" name="barcode_generated" :value="barcodeGenerated ? '1' : '0'">
                        </div>
                    </div>
                </div>

                <!-- STEP 4 (or 3 if service): DESCRIPCIÓN E IMAGEN -->
                <div x-show="step === totalSteps" x-collapse class="space-y-6">
                    
                    <!-- Descripción -->
                    <div class="space-y-1">
                        <label for="description" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Descripción o notas adicionales (Opcional)
                        </label>
                        <textarea id="description" name="description" x-model="description" rows="4"
                                  placeholder="Ej: Pantalla de calidad premium tipo OLED, requiere cambiar el circuito integrado de la pantalla original para evitar aviso de pieza desconocida..."
                                  class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"></textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Escribe notas útiles, advertencias o detalles técnicos sobre esta pieza o servicio.</p>
                    </div>

                    <!-- Imagen -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Foto o Imagen del Producto (Opcional)</label>
                        
                        <!-- Drag and Drop Box -->
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800/70 transition-colors relative">
                            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp,image/gif"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   @change="handleImageChange($event)">
                            
                            <div class="space-y-3" x-show="!imagePreview">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <div class="text-sm leading-6 text-gray-600 dark:text-gray-400">
                                    <span class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Selecciona una foto</span>
                                    <span class="pl-1">o arrástrala aquí</span>
                                </div>
                                <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">Archivos PNG, JPG, WEBP o GIF hasta 5MB</p>
                            </div>

                            <!-- Image Preview -->
                            <div class="space-y-4" x-show="imagePreview" x-cloak>
                                <div class="relative inline-block group">
                                    <img :src="imagePreview" class="max-h-48 rounded-lg border border-gray-200 dark:border-gray-700 mx-auto shadow-sm">
                                    <button type="button" @click="imagePreview = null; document.getElementById('image').value = ''"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white p-1.5 rounded-full shadow-md hover:bg-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">Esta imagen se guardará para el catálogo.</div>
                            </div>
                        </div>
                        @error('image') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Footer Navigation Buttons -->
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6 flex items-center justify-between">
                    
                    <!-- Botón Atrás -->
                    <button type="button" @click="prevStep" x-show="step > 1" 
                        class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="mr-2 -ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                        Atrás
                    </button>
                    <div x-show="step === 1"></div> <!-- Spacer -->

                    <!-- Botón Siguiente -->
                    <button type="button" @click="nextStep" x-show="step < totalSteps"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        Siguiente paso
                        <svg class="ml-2 -mr-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    </button>

                    <!-- Botón Guardar (Paso final) -->
                    <button type="submit" x-show="step === totalSteps"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        Guardar artículo
                        <svg class="ml-2 -mr-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productForm', () => ({
        step: 1,
        type: '{{ old('type', 'producto') }}',
        name: '{{ old('name', '') }}',
        category_id: '{{ old('category_id', '') }}',
        purchase_price: {{ old('purchase_price', 0.00) }},
        sale_price: {{ old('sale_price', 0.00) }},
        has_tax: {{ old('has_tax', 1) ? 'true' : 'false' }},
        tax_percentage: {{ old('tax_percentage', 16.00) }},
        
        // Product fields (Step 3)
        code: '{{ old('code', '') }}',
        barcode: '{{ old('barcode', '') }}',
        barcodeGenerated: false,
        part_number: '{{ old('part_number', '') }}',
        stock: {{ old('stock', 0) }},
        min_stock: {{ old('min_stock', 0) }},
        compatible_brand: '{{ old('compatible_brand', '') }}',
        compatible_model: '{{ old('compatible_model', '') }}',
        
        // Step 4
        description: '{{ old('description', '') }}',
        imagePreview: null,

        init() {
            // Watch changes in type to ensure step bounds
            this.$watch('type', (newType) => {
                const maxSteps = newType === 'producto' ? 4 : 3;
                if (this.step > maxSteps) {
                    this.step = maxSteps;
                }
            });

            // Redirect automatically to the step containing validation errors
            @if ($errors->has('name') || $errors->has('type') || $errors->has('category_id'))
                this.step = 1;
            @elseif ($errors->has('purchase_price') || $errors->has('sale_price') || $errors->has('tax_percentage'))
                this.step = 2;
            @elseif ($errors->has('stock') || $errors->has('min_stock') || $errors->has('compatible_brand') || $errors->has('compatible_model') || $errors->has('code') || $errors->has('barcode') || $errors->has('part_number'))
                this.step = 3;
            @elseif ($errors->has('description') || $errors->has('image'))
                this.step = this.type === 'producto' ? 4 : 3;
            @endif
        },

        get totalSteps() {
            return this.type === 'producto' ? 4 : 3;
        },

        get steps() {
            if (this.type === 'producto') {
                return [
                    'Tipo y Nombre',
                    'Precios y Ganancia',
                    'Inventario y Compatibilidad',
                    'Descripción e Imagen'
                ];
            } else {
                return [
                    'Tipo y Nombre',
                    'Precios y Ganancia',
                    'Descripción e Imagen'
                ];
            }
        },

        get profit() {
            return Math.max(0, this.sale_price - this.purchase_price);
        },

        get profitPercentage() {
            if (!this.purchase_price || this.purchase_price <= 0) {
                return 0;
            }
            return ((this.sale_price - this.purchase_price) / this.purchase_price) * 100;
        },

        nextStep() {
            if (this.validateStep(this.step)) {
                this.step++;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
            }
        },

        validateStep(currentStep) {
            if (currentStep === 1) {
                if (!this.name.trim()) {
                    alert('¡Falta algo! Por favor, escribe el nombre del producto o servicio.');
                    return false;
                }
            } else if (currentStep === 2) {
                if (this.purchase_price === '' || this.purchase_price === null || this.purchase_price < 0) {
                    alert('Por favor, ingresa un precio de compra válido (mínimo 0).');
                    return false;
                }
                if (this.sale_price === '' || this.sale_price === null || this.sale_price < 0) {
                    alert('Por favor, ingresa un precio de venta válido (mínimo 0).');
                    return false;
                }
            } else if (currentStep === 3 && this.type === 'producto') {
                if (this.stock === '' || this.stock === null || this.stock < 0) {
                    alert('Por favor, indica cuántas unidades tienes en existencia (mínimo 0).');
                    return false;
                }
            }
            return true;
        },

        handleImageChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.imagePreview = URL.createObjectURL(file);
            } else {
                this.imagePreview = null;
            }
        },

        generateBarcode() {
            const now = Date.now().toString(36).toUpperCase();
            const rand = Math.floor(Math.random() * 1000000).toString(36).toUpperCase().padStart(4, '0');
            this.barcode = `INT${now}${rand}`;
            this.barcodeGenerated = true;
        }
    }));
});
</script>
@endpush
@endsection

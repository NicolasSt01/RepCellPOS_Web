@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="multiStepForm()">
    <div class="md:flex md:gap-8">
        
        <!-- Left Sidebar / Step Indicators -->
        <div class="hidden md:block w-64 shrink-0">
            <div class="sticky top-24 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">Nueva Orden<br>de Trabajo</h2>
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
                <h1 class="text-xl font-bold text-gray-900 dark:text-white" x-text="steps[step - 1]"></h1>
                <a href="{{ route('work_orders.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    Cancelar
                </a>
            </div>

            <!-- Top Progress Bar (Mobile mostly, or general) -->
            <div class="px-6 pt-6">
                <div class="relative">
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-100 dark:bg-indigo-900/30">
                        <div :style="`width: ${(step / totalSteps) * 100}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-600 transition-all duration-300"></div>
                    </div>
                </div>
            </div>

            <form id="workOrderForm" method="POST" action="{{ route('work_orders.store') }}" enctype="multipart/form-data" @submit.prevent="submitForm" class="p-6">
                @csrf
                <input type="hidden" name="client_id" :value="selectedClient ? selectedClient.id : ''">
                
                <!-- STEP 1: CLIENTE -->
                <div x-show="step === 1" x-collapse>
                    <div class="space-y-6">
                        
                        <div class="flex p-1 bg-gray-100 dark:bg-gray-700/50 rounded-lg w-fit">
                            <button type="button" @click="clientMode = 'search'" 
                                class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
                                :class="clientMode === 'search' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                                Buscar Cliente
                            </button>
                            <button type="button" @click="clientMode = 'create'" 
                                class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
                                :class="clientMode === 'create' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                                Nuevo Cliente
                            </button>
                        </div>

                        <!-- Buscar Cliente -->
                        <div x-show="clientMode === 'search'" class="space-y-4">
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar por nombre o teléfono</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                                    </div>
                                    <input type="text" x-model="searchQuery" @input.debounce.300ms="searchClients" placeholder="Ej: Juan Pérez o 5512345678"
                                        class="block w-full pl-10 rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                                </div>
                                
                                <!-- Search Results Dropdown -->
                                <div x-show="searchResults.length > 0 && searchQuery.length > 1 && !selectedClient" @click.away="searchResults = []"
                                    class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto sm:text-sm">
                                    <template x-for="client in searchResults" :key="client.id">
                                        <div @click="selectClient(client)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-900 dark:text-gray-100">
                                            <div class="flex justify-between">
                                                <span class="font-medium truncate" x-text="client.name"></span>
                                                <span class="text-gray-500 dark:text-gray-400" x-text="client.phone"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Selected Client Card -->
                            <div x-show="selectedClient" class="rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 p-4 flex justify-between items-center">
                                <div>
                                    <h4 class="text-sm font-bold text-indigo-900 dark:text-indigo-200" x-text="selectedClient?.name"></h4>
                                    <p class="text-sm text-indigo-700 dark:text-indigo-300" x-text="selectedClient?.phone"></p>
                                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">
                                        <span x-show="selectedClient?.notification_preference === 'email'">📧 Correo</span>
                                        <span x-show="selectedClient?.notification_preference === 'whatsapp'">💬 WhatsApp</span>
                                        <span x-show="selectedClient?.notification_preference === 'call'">📞 Llamada</span>
                                        <span x-text="selectedClient?.email ? '· ' + selectedClient.email : ''"></span>
                                    </p>
                                </div>
                                <button type="button" @click="selectedClient = null" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium">
                                    Cambiar
                                </button>
                            </div>
                        </div>

                        <!-- Crear Cliente -->
                        <div x-show="clientMode === 'create'" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                                <input type="text" x-model="newClient.name" class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono <span class="text-red-500">*</span></label>
                                <input type="text" x-model="newClient.phone" class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Correo Electrónico</label>
                                <input type="email" x-model="newClient.email" class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notificación por Defecto <span class="text-red-500">*</span></label>
                                <select x-model="newClient.notification_preference" class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                                    @if(Auth::user()->tenant->hasFeature('notifications_whatsapp'))
                                    <option value="whatsapp">WhatsApp</option>
                                    @endif
                                    <option value="email">Correo Electrónico</option>
                                    <option value="call">Llamada Telefónica</option>
                                </select>
                            </div>
                            
                            <div class="sm:col-span-2">
                                <button type="button" @click="createAndSelectClient" :disabled="isCreatingClient" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50">
                                    <svg x-show="isCreatingClient" class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-show="!isCreatingClient">Guardar y Seleccionar Cliente</span>
                                    <span x-show="isCreatingClient">Guardando...</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- STEP 2: EQUIPO -->
                <div x-show="step === 2" x-collapse>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Marca <span class="text-red-500">*</span></label>
                            <input type="text" name="device_brand" x-model="device_brand" placeholder="Ej: Apple, Samsung, Xiaomi"
                                class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Modelo <span class="text-red-500">*</span></label>
                            <input type="text" name="device_model" x-model="device_model" placeholder="Ej: iPhone 13 Pro Max"
                                class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número de Serie</label>
                            <input type="text" name="device_serial" x-model="device_serial" placeholder="N/A si no está disponible"
                                class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IMEI</label>
                            <input type="text" name="device_imei" x-model="device_imei" placeholder="N/A si no está disponible"
                                class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        </div>
                    </div>
                </div>

                <!-- STEP 3: PROBLEMA E IMÁGENES -->
                <div x-show="step === 3" x-collapse>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción del Problema <span class="text-red-500">*</span></label>
                            <textarea name="problem_description" x-model="problem_description" rows="4" 
                                class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"
                                placeholder="Describe detalladamente el problema que presenta el equipo..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Imágenes del Equipo</label>
                            
                            <!-- Webcam / Upload Container -->
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 text-center"
                                :class="cameraActive ? 'bg-black' : 'bg-gray-50 dark:bg-gray-800/50'">
                                
                                <div x-show="!cameraActive" class="space-y-4 py-4">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                    <div class="flex justify-center text-sm leading-6 text-gray-600 dark:text-gray-400">
                                        <label class="relative cursor-pointer rounded-md font-semibold text-indigo-600 dark:text-indigo-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                                            <span>Sube archivos</span>
                                            <input type="file" name="images[]" multiple accept="image/*" class="sr-only" @change="handleFileUpload">
                                        </label>
                                        <p class="pl-1">o usa la</p>
                                        <button type="button" @click="startCamera" class="ml-1 font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">cámara</button>
                                    </div>
                                    <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">PNG, JPG, WEBP hasta 5MB</p>
                                </div>

                                <!-- Camera View -->
                                <div x-show="cameraActive" class="relative">
                                    <video x-ref="videoElement" class="w-full max-w-md mx-auto rounded-lg" autoplay playsinline></video>
                                    <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-4">
                                        <button type="button" @click="takePhoto" class="bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-full shadow-lg">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg>
                                        </button>
                                        <button type="button" @click="stopCamera" class="bg-red-600 hover:bg-red-700 text-white p-3 rounded-full shadow-lg">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                    <canvas x-ref="canvasElement" class="hidden"></canvas>
                                </div>
                            </div>

                            <!-- Image Previews -->
                            <div x-show="capturedImages.length > 0 || fileImages.length > 0" class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <template x-for="(img, i) in capturedImages" :key="'cap'+i">
                                    <div class="relative group">
                                        <img :src="img" class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                        <input type="hidden" name="captured_images[]" :value="img">
                                        <button type="button" @click="removeCapturedImage(i)" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </template>
                                <template x-for="(fImg, i) in fileImages" :key="'fil'+i">
                                    <div class="relative group">
                                        <img :src="fImg.preview" class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                        <button type="button" @click="removeFileImage(i)" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: SEGURIDAD -->
                <div x-show="step === 4" x-collapse>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        
                        <!-- Patrón -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Patrón de Desbloqueo</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Dibuja el patrón conectando los puntos en la cuadrícula 3x3. (1 a 9)</p>
                            
                            <div class="flex justify-center">
                                <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 inline-block touch-none select-none"
                                    x-ref="patternContainer"
                                    @pointerdown="startPattern($event)"
                                    @pointermove="movePattern($event)"
                                    @pointerup="endPattern"
                                    @pointercancel="endPattern"
                                    style="position: relative; width: 240px; height: 240px;">
                                    
                                    <!-- Canvas for drawing lines -->
                                    <canvas x-ref="patternCanvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
                                    
                                    <!-- 3x3 Dots Grid -->
                                    <div class="absolute inset-0 p-6 grid grid-cols-3 grid-rows-3 gap-8 pointer-events-none">
                                        <template x-for="i in 9" :key="i">
                                            <div class="flex items-center justify-center">
                                                <div class="w-4 h-4 rounded-full border-2 transition-colors duration-200"
                                                    :class="pattern.includes(i) ? 'bg-indigo-600 border-indigo-600 scale-150' : 'border-gray-400 dark:border-gray-500 bg-white dark:bg-gray-700'"
                                                    :data-dot="i"
                                                    ></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex items-center justify-between">
                                <input type="hidden" name="unlock_pattern" x-model="unlock_pattern">
                                <span class="text-sm font-mono bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded text-gray-600 dark:text-gray-300" x-text="unlock_pattern || 'No definido'"></span>
                                <button type="button" @click="clearPattern" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium">Limpiar Patrón</button>
                            </div>
                        </div>

                        <!-- PIN -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PIN o Contraseña</label>
                            <input type="text" name="unlock_pin" x-model="unlock_pin" placeholder="Ej: 1234 o 123456"
                                class="block w-full rounded-md border-0 py-2 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Si el equipo no tiene patrón, ingresa el PIN o contraseña aquí. Si está apagado o no se puede probar, déjalo vacío.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6 flex items-center justify-between">
                    <button type="button" @click="prevStep" x-show="step > 1" 
                        class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Atrás
                    </button>
                    <div x-show="step === 1"></div> <!-- Spacer -->

                    <button type="button" @click="nextStep" x-show="step < totalSteps"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        Siguiente
                        <svg class="ml-2 -mr-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    </button>

                    <button type="submit" x-show="step === totalSteps" :disabled="isSubmitting"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors">
                        <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Crear Orden de Trabajo
                        <svg x-show="!isSubmitting" class="ml-2 -mr-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('multiStepForm', () => ({
        step: 1,
        totalSteps: 4,
        steps: [
            'Información del Cliente',
            'Información del Equipo',
            'Problema e Imágenes',
            'Seguridad'
        ],
        isSubmitting: false,

        // Step 1: Client
        clientMode: 'search',
        searchQuery: '',
        searchResults: [],
        selectedClient: null,
        newClient: { name: '', phone: '', email: '', notification_preference: 'whatsapp' },
        isCreatingClient: false,

        // Step 2: Device
        device_brand: '',
        device_model: '',
        device_serial: '',
        device_imei: '',

        // Step 3: Problem & Images
        problem_description: '',
        cameraActive: false,
        cameraStream: null,
        capturedImages: [],
        fileImages: [],

        // Step 4: Security
        unlock_pattern: '',
        unlock_pin: '',
        pattern: [],
        isDrawing: false,
        ctx: null,

        init() {
            this.$watch('step', (val) => {
                if ( val === 4) {
                    this.$nextTick(() => {
                        requestAnimationFrame(() => this.initPatternCanvas());
                    });
                } else {
                    this.stopCamera();
                }
            });
        },

        nextStep() {
            if (this.validateStep(this.step)) {
                this.step++;
            }
        },

        prevStep() {
            if (this.step > 1) this.step--;
        },

        validateStep(step) {
            // Very basic validation logic for UX
            if (step === 1) {
                if (!this.selectedClient) {
                    alert('Debes seleccionar o crear un cliente primero.');
                    return false;
                }
            } else if (step === 2) {
                if (!this.device_brand || !this.device_model) {
                    alert('La marca y el modelo son obligatorios.');
                    return false;
                }
            } else if (step === 3) {
                if (!this.problem_description) {
                    alert('Debes describir el problema.');
                    return false;
                }
            }
            return true;
        },

        async searchClients() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }
            try {
                const res = await fetch(`/work_orders/search-clients?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await res.json();
                this.searchResults = data;
            } catch (e) {
                console.error(e);
            }
        },

        selectClient(client) {
            this.selectedClient = client;
            this.searchQuery = '';
            this.searchResults = [];
        },

        async createAndSelectClient() {
            if (!this.newClient.name || !this.newClient.phone) {
                alert('Nombre y teléfono son obligatorios.');
                return;
            }
            this.isCreatingClient = true;
            try {
                const res = await fetch('/work_orders/store-client', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newClient)
                });
                const data = await res.json();
                if (res.ok) {
                    this.selectedClient = data;
                    this.clientMode = 'search';
                } else {
                    alert('Error: ' + (data.message || 'No se pudo crear el cliente'));
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.isCreatingClient = false;
            }
        },

        // --- Camera Logic ---
        async startCamera() {
            try {
                this.cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                this.$refs.videoElement.srcObject = this.cameraStream;
                this.cameraActive = true;
            } catch (err) {
                console.error("Error accessing camera: ", err);
                alert("No se pudo acceder a la cámara. Verifica los permisos o usa la opción de subir archivos.");
            }
        },

        stopCamera() {
            if (this.cameraStream) {
                this.cameraStream.getTracks().forEach(track => track.stop());
                this.cameraStream = null;
            }
            this.cameraActive = false;
        },

        takePhoto() {
            if (!this.cameraActive || this.capturedImages.length >= 5) return;
            const video = this.$refs.videoElement;
            const canvas = this.$refs.canvasElement;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            // Convert to base64 jpeg
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            this.capturedImages.push(dataUrl);
            
            // Optional UX: Flash effect
            video.style.opacity = 0;
            setTimeout(() => video.style.opacity = 1, 100);
        },

        removeCapturedImage(index) {
            this.capturedImages.splice(index, 1);
        },

        handleFileUpload(e) {
            const files = Array.from(e.target.files);
            const remainingSlots = 5 - (this.capturedImages.length + this.fileImages.length);
            
            files.slice(0, remainingSlots).forEach(file => {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.fileImages.push({
                        file: file,
                        preview: ev.target.result
                    });
                };
                reader.readAsDataURL(file);
            });
        },

        removeFileImage(index) {
            this.fileImages.splice(index, 1);
            // Sync with actual file input is hard in plain HTML, so we just rely on form submission 
            // taking the files directly if we can, but since we modify array, we must intercept submit 
            // and use FormData if we want exact control over dropped files.
            // Since this is standard input type=file, removing here won't remove from the input.files.
            // For simplicity, we just clear the input on change, and build FormData on submit.
        },

        // --- Pattern Lock Logic ---
        initPatternCanvas() {
            const canvas = this.$refs.patternCanvas;
            const container = this.$refs.patternContainer;
            canvas.width = container.clientWidth;
            canvas.height = container.clientHeight;
            this.ctx = canvas.getContext('2d');
            this.ctx.lineWidth = 4;
            this.ctx.strokeStyle = '#4f46e5'; // indigo-600
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.clearPattern();
        },

        getDotPositions() {
            const positions = [];
            const container = this.$refs.patternContainer;
            const containerRect = container.getBoundingClientRect();
            container.querySelectorAll('[data-dot]').forEach(dot => {
                const i = parseInt(dot.dataset.dot);
                const rect = dot.getBoundingClientRect();
                positions[i] = {
                    x: rect.left - containerRect.left + rect.width / 2,
                    y: rect.top - containerRect.top + rect.height / 2
                };
            });
            return positions;
        },

        startPattern(e) {
            this.isDrawing = true;
            this.pattern = [];
            this.unlock_pattern = '';
            this.ctx.clearRect(0, 0, this.$refs.patternCanvas.width, this.$refs.patternCanvas.height);
            this.checkIntersection(e);
        },

        movePattern(e) {
            if (!this.isDrawing) return;
            
            const rect = this.$refs.patternContainer.getBoundingClientRect();
            const currentX = e.clientX - rect.left;
            const currentY = e.clientY - rect.top;
            
            this.checkIntersection(e);
            this.drawLines(currentX, currentY);
        },

        endPattern() {
            this.isDrawing = false;
            this.unlock_pattern = this.pattern.join('');
            this.drawLines(); // draw final without trailing line to cursor
        },

        checkIntersection(e) {
            const rect = this.$refs.patternContainer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const positions = this.getDotPositions();
            const threshold = 25; // Hit radius

            for (let i = 1; i <= 9; i++) {
                if (!this.pattern.includes(i)) {
                    const pos = positions[i];
                    if (pos) {
                        const dist = Math.hypot(pos.x - x, pos.y - y);
                        if (dist < threshold) {
                            this.pattern.push(i);
                            // Haptic feedback
                            if (window.navigator && window.navigator.vibrate) {
                                window.navigator.vibrate(20);
                            }
                        }
                    }
                }
            }
        },

        drawLines(cursorX = null, cursorY = null) {
            if (!this.ctx) return;
            this.ctx.clearRect(0, 0, this.$refs.patternCanvas.width, this.$refs.patternCanvas.height);
            
            if (this.pattern.length === 0) return;
            
            const positions = this.getDotPositions();
            
            this.ctx.beginPath();
            const startPos = positions[this.pattern[0]];
            this.ctx.moveTo(startPos.x, startPos.y);
            
            for (let i = 1; i < this.pattern.length; i++) {
                const pos = positions[this.pattern[i]];
                this.ctx.lineTo(pos.x, pos.y);
            }
            
            if (this.isDrawing && cursorX !== null && cursorY !== null) {
                this.ctx.lineTo(cursorX, cursorY);
            }
            
            this.ctx.stroke();
        },

        clearPattern() {
            this.pattern = [];
            this.unlock_pattern = '';
            if(this.ctx) {
                this.ctx.clearRect(0, 0, this.$refs.patternCanvas.width, this.$refs.patternCanvas.height);
            }
        },

        // --- Submit Logic ---
        submitForm(e) {
            if (!this.validateStep(this.step)) return;
            
            this.isSubmitting = true;
            
            // Build FormData to manually include fileImages array correctly
            const form = e.target;
            const formData = new FormData(form);
            
            // Remove the default images[] because we manually handled them
            formData.delete('images[]');
            
            // Append file images
            this.fileImages.forEach((fObj) => {
                formData.append('images[]', fObj.file);
            });

            // Submit via standard POST but avoiding default to include the modified FormData
            // The easiest way is to use fetch and redirect on success.
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    const data = await response.json();
                    if(data.redirect) window.location.href = data.redirect;
                    else window.location.href = '{{ route("work_orders.index") }}';
                } else {
                    const err = await response.json();
                    alert('Error al guardar: ' + (err.message || 'Revisa los campos requeridos.'));
                    this.isSubmitting = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al enviar el formulario.');
                this.isSubmitting = false;
            });
        }
    }));
});
</script>
@endpush
@endsection

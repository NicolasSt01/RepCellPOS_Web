@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Nueva Orden de Trabajo</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Registrar recepción de equipo para reparación</p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <form method="POST" action="{{ route('work_orders.store') }}" class="p-6 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Cliente <span class="text-red-500">*</span></label>
                <select name="client_id" required
                    class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    <option value="">Seleccionar cliente...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }} — {{ $client->phone }}
                        </option>
                    @endforeach
                </select>
                @error('client_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Datos del Dispositivo</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="device_brand" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Marca <span class="text-red-500">*</span></label>
                        <input type="text" name="device_brand" id="device_brand" value="{{ old('device_brand') }}" required placeholder="Ej: Apple, Samsung, Xiaomi"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        @error('device_brand') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="device_model" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Modelo <span class="text-red-500">*</span></label>
                        <input type="text" name="device_model" id="device_model" value="{{ old('device_model') }}" required placeholder="Ej: iPhone 13 Pro Max"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        @error('device_model') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="device_serial" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Número de Serie</label>
                        <input type="text" name="device_serial" id="device_serial" value="{{ old('device_serial') }}" placeholder="N/A si no está disponible"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        @error('device_serial') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="device_imei" class="block text-sm font-medium text-gray-900 dark:text-gray-100">IMEI</label>
                        <input type="text" name="device_imei" id="device_imei" value="{{ old('device_imei') }}" placeholder="N/A si no está disponible"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        @error('device_imei') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="unlock_pattern" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Patrón de Desbloqueo</label>
                        <input type="text" name="unlock_pattern" id="unlock_pattern" value="{{ old('unlock_pattern') }}" placeholder="N/A si el equipo está apagado"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        @error('unlock_pattern') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="unlock_pin" class="block text-sm font-medium text-gray-900 dark:text-gray-100">PIN</label>
                        <input type="text" name="unlock_pin" id="unlock_pin" value="{{ old('unlock_pin') }}" placeholder="N/A si el equipo está apagado"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        @error('unlock_pin') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div>
                <label for="problem_description" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Descripción del Problema <span class="text-red-500">*</span></label>
                <textarea name="problem_description" id="problem_description" rows="4" required
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"
                    placeholder="Describir el problema reportado por el cliente...">{{ old('problem_description') }}</textarea>
                @error('problem_description') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('work_orders.index') }}"
                    class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    Registrar orden
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Datos de la Empresa</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Información de tu taller que aparece en comprobantes y notificaciones</p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <form method="POST" action="{{ route('settings.company.update') }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Nombre del taller <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $tenant->name) }}" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Teléfono</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $tenant->phone) }}"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Correo electrónico</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $tenant->email) }}"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Slug (único)</label>
                    <input type="text" value="{{ $tenant->slug }}" disabled
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-500 dark:text-gray-400 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 bg-gray-50 dark:bg-gray-600 sm:text-sm sm:leading-6">
                    <p class="mt-1 text-xs text-gray-500">Identificador único de tu taller (no editable)</p>
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Dirección</label>
                <textarea name="address" id="address" rows="3"
                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">{{ old('address', $tenant->address) }}</textarea>
                @error('address') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

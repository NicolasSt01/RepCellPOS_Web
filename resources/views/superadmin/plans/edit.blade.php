@extends('layouts.app')

@php $knownFeatures = config('plans.features'); @endphp
@php $knownLimits = config('plans.limits'); @endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.plans.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">&larr; Volver a planes</a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Editar Plan: {{ $plan->name }}</h1>
        </div>
        <form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre del Plan</label>
                <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" required
                       class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio Mensual ($)</label>
                <input type="number" name="price" id="price" value="{{ old('price', $plan->price) }}" step="0.01" min="0" required
                       class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                <textarea name="description" id="description" rows="2"
                          class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $plan->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <h2 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Características del Plan</h2>
                <div class="space-y-3 bg-gray-50 dark:bg-gray-900/20 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    @foreach($knownFeatures as $key => $label)
                    @php $featureVal = old("features.{$key}", $plan->features[$key] ?? false); @endphp
                    <label class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="features[{{ $key }}]" value="0">
                            <input type="checkbox" name="features[{{ $key }}]" value="1"
                                   {{ $featureVal ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('features') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <h2 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Límites del Plan</h2>
                <div class="space-y-3 bg-gray-50 dark:bg-gray-900/20 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    @foreach($knownLimits as $key => $config)
                    @php $limitVal = old("limits.{$key}", $plan->limits[$key] ?? ''); @endphp
                    <div>
                        <label for="limit_{{ $key }}" class="block text-sm text-gray-700 dark:text-gray-300 mb-1">
                            {{ $config['label'] }}
                            @if($config['unlimited'])
                            <span class="text-xs text-gray-400">(-1 = ilimitado)</span>
                            @endif
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="limits[{{ $key }}]" id="limit_{{ $key }}"
                                   value="{{ $limitVal }}"
                                   class="block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @if($config['suffix'])
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $config['suffix'] }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @error('limits') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Orden</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" min="0"
                       class="block w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('sort_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Activo</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_highlight" value="1" {{ old('is_highlight', $plan->is_highlight) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Destacado</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('admin.plans.index') }}"
                   class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@extends('layouts.app')

@php $knownFeatures = config('plans.features'); @endphp
@php $knownLimits = config('plans.limits'); @endphp

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Planes de Suscripción</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gestiona los planes disponibles para los tenants</p>
        </div>
        <a href="{{ route('admin.plans.create') }}"
           class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nuevo Plan
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($plans as $plan)
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden flex flex-col {{ $plan->is_highlight ? 'ring-2 ring-indigo-500' : '' }}">
            @if($plan->is_highlight)
            <div class="bg-indigo-600 text-center py-1">
                <span class="text-xs font-semibold text-white uppercase tracking-wider">Destacado</span>
            </div>
            @endif
            <div class="p-6 flex-1 flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                    @if(!$plan->is_active)
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Inactivo</span>
                    @endif
                </div>
                <div class="mb-4">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($plan->price, 2) }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">/mes</span>
                </div>
                @if($plan->description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $plan->description }}</p>
                @endif

                @if($plan->features)
                <div class="mb-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Características</p>
                    <ul class="space-y-1.5">
                        @foreach($knownFeatures as $key => $label)
                        @php $enabled = $plan->features[$key] ?? false; @endphp
                        <li class="flex items-center gap-2 text-sm {{ $enabled ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500' }}">
                            @if($enabled)
                            <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            @else
                            <svg class="h-4 w-4 text-gray-300 dark:text-gray-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            @endif
                            {{ $label }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if($plan->limits)
                <div class="mb-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Límites</p>
                    <ul class="space-y-1">
                        @foreach($knownLimits as $key => $config)
                        @php $value = $plan->limits[$key] ?? null; @endphp
                        @if($value !== null)
                        <li class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $config['label'] }}:
                            @if($value === -1)
                            <span class="font-medium text-indigo-600 dark:text-indigo-400">Ilimitado</span>
                            @else
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ number_format($value) }}</span>
                            @if($config['suffix']) {{ $config['suffix'] }} @endif
                            @endif
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700 mt-auto">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $plan->tenants_count ?? 0 }} tenant(s)
                    </span>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.plans.edit', $plan) }}"
                           class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                            Editar
                        </a>
                        <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="inline"
                              onsubmit="return confirm('¿Eliminar el plan {{ $plan->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-900/20 px-3 py-1.5 text-xs font-semibold text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/40">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">No hay planes</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Crea tu primer plan de suscripción.</p>
                <a href="{{ route('admin.plans.create') }}" class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Crear Plan
                </a>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
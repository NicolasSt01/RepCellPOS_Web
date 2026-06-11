@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">&larr; Volver a {{ $tenant->name }}</a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Editar Suscripción — {{ $tenant->name }}</h1>

        <form action="{{ route('admin.subscriptions.update', [$tenant, $subscription]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="plan_type" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Plan <span class="text-red-500">*</span></label>
                    <select name="plan_type" id="plan_type" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="mensual" @selected(old('plan_type', $subscription->plan_type) === 'mensual')>Mensual</option>
                        <option value="anual" @selected(old('plan_type', $subscription->plan_type) === 'anual')>Anual</option>
                        <option value="prueba" @selected(old('plan_type', $subscription->plan_type) === 'prueba')>Prueba</option>
                        <option value="personalizado" @selected(old('plan_type', $subscription->plan_type) === 'personalizado')>Personalizado</option>
                    </select>
                    @error('plan_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Monto <span class="text-red-500">*</span></label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 sm:text-sm">$</span>
                        <input type="number" step="0.01" min="0" name="amount" id="amount" value="{{ old('amount', $subscription->amount) }}" required
                               class="block w-full pl-8 rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    </div>
                    @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Fecha de inicio <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $subscription->start_date->toDateString()) }}" required
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Fecha de fin</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $subscription->end_date?->toDateString()) }}"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Estado <span class="text-red-500">*</span></label>
                    <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="activa" @selected(old('status', $subscription->status) === 'activa')>Activa</option>
                        <option value="pendiente" @selected(old('status', $subscription->status) === 'pendiente')>Pendiente</option>
                        <option value="expirada" @selected(old('status', $subscription->status) === 'expirada')>Expirada</option>
                        <option value="cancelada" @selected(old('status', $subscription->status) === 'cancelada')>Cancelada</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Notas</label>
                <textarea name="notes" id="notes" rows="3"
                          class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">{{ old('notes', $subscription->notes) }}</textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.tenants.show', $tenant) }}" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</a>
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Actualizar Suscripción</button>
            </div>
        </form>
    </div>
</div>
@endsection

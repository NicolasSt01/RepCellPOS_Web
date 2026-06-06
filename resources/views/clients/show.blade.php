@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $client->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detalle del cliente</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('clients.edit', $client) }}"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                Editar
            </a>
            <a href="{{ route('clients.index') }}"
                class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Volver
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-6">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->phone }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Correo electrónico</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Preferencia de notificación</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                            @if($client->notification_preference === 'whatsapp') bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400
                            @elseif($client->notification_preference === 'email') bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400
                            @else bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400
                            @endif">
                            {{ ucfirst($client->notification_preference) }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->notes ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Registrado</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $client->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Historial de Órdenes de Trabajo</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Las órdenes de trabajo de este cliente aparecerán aquí una vez que el módulo esté implementado.</p>
        </div>
    </div>
</div>
@endsection

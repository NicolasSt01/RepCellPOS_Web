@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Clientes</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestión de clientes del taller</p>
        </div>
        <a href="{{ route('clients.create') }}"
            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            Nuevo cliente
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('clients.index') }}" class="flex gap-3">
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    placeholder="Buscar por nombre, teléfono o email..."
                    class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Buscar
                </button>
                @if($search ?? false)
                <a href="{{ route('clients.index') }}"
                    class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Limpiar
                </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Notificación</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($clients as $client)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $client->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $client->phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $client->email ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                @if($client->notification_preference === 'whatsapp') bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400
                                @elseif($client->notification_preference === 'email') bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400
                                @else bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400
                                @endif">
                                {{ ucfirst($client->notification_preference) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('clients.show', $client) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Ver</a>
                                <a href="{{ route('clients.edit', $client) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Editar</a>
                                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('¿Eliminar este cliente?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-500">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            @if($search ?? false)
                                No se encontraron clientes que coincidan con "{{ $search }}"
                            @else
                                No hay clientes registrados. <a href="{{ route('clients.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Crear el primero</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3">
            {{ $clients->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

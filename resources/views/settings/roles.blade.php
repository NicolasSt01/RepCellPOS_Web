@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Roles y Permisos</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestiona los roles y asigna permisos granulares</p>
        </div>
        <button onclick="document.getElementById('create-role-modal').classList.remove('hidden')"
            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            Nuevo rol
        </button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @foreach($roles as $role)
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $role->name }}</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $role->permissions->count() }} permisos</span>
                </div>
                <form method="POST" action="{{ route('settings.roles.update', $role) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="max-h-64 overflow-y-auto space-y-2">
                        @foreach($permissions as $permission)
                        <div class="flex items-center">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm-{{ $role->id }}-{{ $permission->id }}"
                                {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                            <label for="perm-{{ $role->id }}-{{ $permission->id }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $permission->name }}</label>
                        </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end pt-3 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                            Guardar permisos
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div id="create-role-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Nuevo Rol</h3>
            <form method="POST" action="{{ route('settings.roles.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Nombre del rol <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Ej: Supervisor, Cajero"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="this.closest('[role=dialog]').classList.add('hidden')" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Crear rol</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

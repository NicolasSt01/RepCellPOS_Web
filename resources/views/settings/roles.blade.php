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
                <form method="POST" action="{{ route('settings.roles.update', $role) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between mb-4">
                        <input type="text" name="name" value="{{ $role->name }}" required
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 sm:text-sm sm:leading-6">
                        <span class="text-sm text-gray-500 dark:text-gray-400 ml-2 whitespace-nowrap">{{ $role->permissions->count() }} permisos</span>
                    </div>
                    @php
                    $permDescriptions = [
                        'clients.view' => 'Ver lista de clientes',
                        'clients.create' => 'Registrar nuevos clientes',
                        'clients.edit' => 'Editar datos de clientes',
                        'clients.delete' => 'Eliminar clientes',
                        'work_orders.view' => 'Ver órdenes de trabajo',
                        'work_orders.create' => 'Crear nuevas órdenes de trabajo',
                        'work_orders.edit' => 'Editar órdenes existentes',
                        'work_orders.change_status' => 'Cambiar estado de las órdenes',
                        'work_orders.set_priority' => 'Asignar prioridad a las órdenes',
                        'work_orders.add_notes' => 'Agregar anotaciones a las órdenes',
                        'quotes.view' => 'Ver cotizaciones',
                        'quotes.create' => 'Crear cotizaciones',
                        'quotes.approve' => 'Aprobar o rechazar cotizaciones',
                        'products.view' => 'Ver catálogo de productos',
                        'products.create' => 'Agregar nuevos productos',
                        'products.edit' => 'Editar productos existentes',
                        'products.delete' => 'Eliminar productos',
                        'kardex.view' => 'Ver movimientos del inventario',
                        'kardex.adjust' => 'Hacer ajustes manuales de inventario',
                        'pos.access' => 'Usar el módulo de ventas (POS)',
                        'pos.sell' => 'Realizar cobros en caja',
                        'pos.charge_orders' => 'Cobrar órdenes de trabajo desde POS',
                        'pos.apply_discounts' => 'Aplicar descuentos en ventas',
                        'cash_register.open' => 'Abrir caja (registrar fondo inicial)',
                        'cash_register.close' => 'Cerrar caja al final del día',
                        'cash_register.withdraw' => 'Retirar efectivo de la caja',
                        'cash_register.view_history' => 'Ver historial de la caja',
                        'reports.sales' => 'Ver reportes de ventas',
                        'reports.work_orders' => 'Ver reportes de órdenes',
                        'reports.analytics' => 'Ver análisis y estadísticas',
                        'settings.company' => 'Editar datos del taller (logo, nombre, redes)',
                        'settings.clauses' => 'Editar cláusulas que se imprimen en comprobantes',
                        'settings.taxes' => 'Configurar impuestos y formato de impresión',
                        'settings.users' => 'Administrar usuarios del sistema',
                        'settings.roles' => 'Gestionar roles y permisos',
                    ];
                    @endphp
                    <div class="max-h-64 overflow-y-auto space-y-2">
                        @foreach($permissions as $permission)
                        <div class="flex items-start">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm-{{ $role->id }}-{{ $permission->id }}"
                                {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                            <label for="perm-{{ $role->id }}-{{ $permission->id }}" class="ml-2 text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $permDescriptions[$permission->name] ?? $permission->name }}</span>
                                <span class="block text-xs text-gray-400 dark:text-gray-500">{{ $permission->name }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end pt-3 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                            Guardar permisos
                        </button>
                    </div>
                </form>
                @if($role->name !== 'super-admin' && $role->name !== 'admin')
                <form action="{{ route('settings.roles.destroy', $role) }}" method="POST" class="mt-3"
                      onsubmit="return confirm('¿Eliminar el rol &quot;{{ $role->name }}&quot;? Los usuarios con este rol quedarán sin rol asignado.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-500 text-sm font-medium">Eliminar</button>
                </form>
                @endif
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

@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Cláusulas y Políticas</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Términos y condiciones que se imprimen en los comprobantes</p>
        </div>
        <button onclick="document.getElementById('create-clause-modal').classList.remove('hidden')"
            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            Nueva cláusula
        </button>
    </div>

    <div class="space-y-4">
        @forelse($clauses as $clause)
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $clause->title }}</h3>
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-gray-50 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                {{ ucfirst($clause->type) }}
                            </span>
                            @if($clause->is_active)
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">Activa</span>
                            @else
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400">Inactiva</span>
                            @endif
                            @if($clause->print_on_receipt)
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">Se imprime</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $clause->content }}</p>
                    </div>
                    <div class="flex gap-2 ml-4">
                        <button onclick="editClause({{ $clause->id }}, '{{ addslashes($clause->title) }}', '{{ addslashes($clause->content) }}', '{{ $clause->type }}', {{ $clause->is_active ? 'true' : 'false' }}, {{ $clause->print_on_receipt ? 'true' : 'false' }})"
                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 text-sm">Editar</button>
                        <form method="POST" action="{{ route('settings.clauses.destroy', $clause) }}" class="inline" onsubmit="return confirm('¿Eliminar esta cláusula?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-500 text-sm">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-12 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">No hay cláusulas registradas. Crea la primera para que aparezca en los comprobantes.</p>
        </div>
        @endforelse
    </div>
</div>

<div id="create-clause-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Nueva Cláusula</h3>
            <form method="POST" action="{{ route('settings.clauses.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Título <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Ej: Política de garantía"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Tipo <span class="text-red-500">*</span></label>
                    <select name="type" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="terms">Términos y condiciones</option>
                        <option value="warranty">Política de garantía</option>
                        <option value="privacy">Aviso de privacidad</option>
                        <option value="return">Política de devolución</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Contenido <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="6" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"></textarea>
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="print_on_receipt" value="0">
                    <input type="checkbox" name="print_on_receipt" value="1" checked
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                    <label class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Imprimir en comprobantes</label>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="this.closest('[role=dialog]').classList.add('hidden')" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Crear cláusula</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-clause-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Editar Cláusula</h3>
            <form id="edit-clause-form" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Título <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="edit-title" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Tipo <span class="text-red-500">*</span></label>
                    <select name="type" id="edit-type" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="terms">Términos y condiciones</option>
                        <option value="warranty">Política de garantía</option>
                        <option value="privacy">Aviso de privacidad</option>
                        <option value="return">Política de devolución</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Contenido <span class="text-red-500">*</span></label>
                    <textarea name="content" id="edit-content" rows="6" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"></textarea>
                </div>
                <div class="flex items-center gap-6">
                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="edit-is-active" value="1"
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                        <label for="edit-is-active" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Activa</label>
                    </div>
                    <div class="flex items-center">
                        <input type="hidden" name="print_on_receipt" value="0">
                        <input type="checkbox" name="print_on_receipt" id="edit-print" value="1"
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                        <label for="edit-print" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Imprimir en comprobantes</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="this.closest('[role=dialog]').classList.add('hidden')" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editClause(id, title, content, type, isActive, printOnReceipt) {
    document.getElementById('edit-clause-form').action = '/settings/clauses/' + id;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-content').value = content;
    document.getElementById('edit-type').value = type;
    document.getElementById('edit-is-active').checked = isActive;
    document.getElementById('edit-print').checked = printOnReceipt;
    document.getElementById('edit-clause-modal').classList.remove('hidden');
}
</script>
@endsection

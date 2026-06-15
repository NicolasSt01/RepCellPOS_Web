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
                            @if($clause->has_file)
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400">PDF</span>
                            @endif
                        </div>
                        @if($clause->has_file)
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <a href="{{ $clause->file_url }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 underline">Ver archivo: {{ $clause->file_name }}</a>
                            </p>
                        @else
                            <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $clause->content }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2 ml-4">
                        <button onclick="editClause({{ $clause->id }}, '{{ addslashes($clause->title) }}', '{{ addslashes($clause->content) }}', '{{ $clause->type }}', {{ $clause->is_active ? 'true' : 'false' }}, {{ $clause->print_on_receipt ? 'true' : 'false' }}, {{ $clause->has_file ? 'true' : 'false' }}, '{{ addslashes($clause->file_name ?? '') }}', '{{ addslashes($clause->file_url ?? '') }}')"
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
            <form method="POST" action="{{ route('settings.clauses.store') }}" enctype="multipart/form-data" class="space-y-4">
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
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Tipo de contenido</label>
                    <select id="create-content-type" onchange="toggleCreateContentType(this.value)"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="text">Texto</option>
                        <option value="file">Archivo PDF</option>
                    </select>
                </div>
                <div id="create-text-content">
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Contenido</label>
                    <textarea name="content" id="create-content" rows="6"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"></textarea>
                </div>
                <div id="create-file-content" class="hidden">
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Archivo PDF</label>
                    <input type="file" name="file" accept=".pdf"
                        class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PDF, máximo 10MB</p>
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
            <form id="edit-clause-form" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Tipo de contenido</label>
                    <select id="edit-content-type" onchange="toggleEditContentType(this.value)"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="text">Texto</option>
                        <option value="file">Archivo PDF</option>
                    </select>
                </div>
                <div id="edit-text-content">
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Contenido</label>
                    <textarea name="content" id="edit-content" rows="6"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"></textarea>
                </div>
                <div id="edit-file-content" class="hidden">
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Archivo PDF</label>
                    <div id="edit-current-file" class="mb-2 hidden">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Archivo actual:
                            <a id="edit-file-link" href="#" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 underline"></a>
                        </p>
                    </div>
                    <input type="file" name="file" accept=".pdf"
                        class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PDF, máximo 10MB. Sube un archivo nuevo para reemplazar el actual.</p>
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
function toggleCreateContentType(value) {
    document.getElementById('create-text-content').classList.toggle('hidden', value !== 'text');
    document.getElementById('create-file-content').classList.toggle('hidden', value !== 'file');
    document.getElementById('create-content').required = (value === 'text');
}

function toggleEditContentType(value) {
    document.getElementById('edit-text-content').classList.toggle('hidden', value !== 'text');
    document.getElementById('edit-file-content').classList.toggle('hidden', value !== 'file');
    document.getElementById('edit-content').required = (value === 'text');
}

function editClause(id, title, content, type, isActive, printOnReceipt, hasFile, fileName, fileUrl) {
    document.getElementById('edit-clause-form').action = '/settings/clauses/' + id;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-content').value = content;
    document.getElementById('edit-type').value = type;
    document.getElementById('edit-is-active').checked = isActive;
    document.getElementById('edit-print').checked = printOnReceipt;

    var contentType = document.getElementById('edit-content-type');
    if (hasFile) {
        contentType.value = 'file';
        document.getElementById('edit-text-content').classList.add('hidden');
        document.getElementById('edit-file-content').classList.remove('hidden');
        document.getElementById('edit-content').required = false;
        document.getElementById('edit-current-file').classList.remove('hidden');
        document.getElementById('edit-file-link').textContent = fileName;
        document.getElementById('edit-file-link').href = fileUrl;
    } else {
        contentType.value = 'text';
        document.getElementById('edit-text-content').classList.remove('hidden');
        document.getElementById('edit-file-content').classList.add('hidden');
        document.getElementById('edit-content').required = true;
        document.getElementById('edit-current-file').classList.add('hidden');
    }

    document.getElementById('edit-clause-modal').classList.remove('hidden');
}
</script>
@endsection

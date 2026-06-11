@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Datos de la Empresa</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Información de tu taller que aparece en comprobantes y notificaciones</p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="p-6 space-y-6" x-data="companyForm()">
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
                    <label for="address" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Dirección</label>
                    <input type="text" name="address" id="address" value="{{ old('address', $tenant->address) }}"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('address') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 mb-2">Logo del taller</label>
                <div class="flex items-start gap-4">
                    <div class="relative">
                        <template x-if="!logoPreview">
                            <div class="w-24 h-24 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                                @if($tenant->logo)
                                <img src="{{ route('r2.serve', ['path' => $tenant->logo]) }}" alt="Logo" class="w-full h-full object-contain rounded-lg">
                                @else
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" /></svg>
                                @endif
                            </div>
                        </template>
                        <template x-if="logoPreview">
                            <div class="relative">
                                <img :src="logoPreview" alt="Vista previa" class="w-24 h-24 object-contain rounded-lg border-2 border-indigo-400 shadow-md">
                                <button type="button" @click="clearLogo()"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs font-bold shadow hover:bg-red-600 transition-colors">
                                    ✕
                                </button>
                            </div>
                        </template>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="logo" id="logo" accept="image/jpg,image/jpeg,image/png,image/webp"
                               @change="previewLogo($event)"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 dark:hover:file:bg-indigo-900/50">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG o WebP · Máximo 2MB</p>
                        @error('logo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 mb-2">Redes Sociales</label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Agrega los enlaces a las redes sociales de tu taller. Aparecerán en los comprobantes.</p>
                <template x-for="(item, index) in socialItems" :key="index">
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" x-model="item.platform" placeholder="Ej: Facebook, Instagram, WhatsApp"
                            class="block w-40 rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <input type="text" x-model="item.url" placeholder="https://facebook.com/tutienda"
                            class="block flex-1 rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <button type="button" @click="removeSocial(index)"
                            class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="addSocial()"
                    class="inline-flex items-center gap-1 mt-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Agregar red social
                </button>
                <input type="hidden" name="social_media" :value="JSON.stringify(socialItems)">
                @error('social_media')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
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

<script>
function companyForm() {
    const initialSocial = @json($tenant->social_media ?? []);
    return {
        logoPreview: null,
        socialItems: Array.isArray(initialSocial) ? initialSocial : [],
        previewLogo(event) {
            const file = event.target.files[0];
            if (!file) { this.logoPreview = null; return; }
            const reader = new FileReader();
            reader.onload = (e) => { this.logoPreview = e.target.result; };
            reader.readAsDataURL(file);
        },
        clearLogo() {
            this.logoPreview = null;
            document.getElementById('logo').value = '';
        },
        addSocial() {
            this.socialItems.push({ platform: '', url: '' });
        },
        removeSocial(index) {
            this.socialItems.splice(index, 1);
        },
    }
}
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="settingsApp()">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">⚙️ Configuración</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Todo lo que necesitas para poner a punto tu taller</p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 rounded-2xl p-3 sm:p-4 overflow-x-auto">
        <div class="flex items-center justify-center gap-0 sm:gap-1 min-w-max mx-auto">
            <template x-for="(step, index) in steps" :key="index">
                <div class="flex items-center">
                    <button @click="activeStep = index"
                        :class="activeStep === index ? 'bg-indigo-600 text-white ring-2 ring-indigo-200 scale-105 sm:scale-110' : (index < activeStep ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400')"
                        class="flex items-center gap-1 sm:gap-2 rounded-full px-2 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-bold transition-all duration-200 hover:scale-105">
                        <span class="flex items-center justify-center w-5 h-5 sm:w-7 sm:h-7 rounded-full text-[10px] sm:text-xs font-black shrink-0"
                            :class="activeStep === index ? 'bg-white/20' : (index < activeStep ? 'bg-white/20' : 'bg-gray-300 dark:bg-gray-600')"
                            x-text="index + 1"></span>
                        <span class="hidden sm:inline" x-text="step"></span>
                    </button>
                    <template x-if="index < steps.length - 1">
                        <div class="w-3 sm:w-10 h-0.5 mx-0.5 sm:mx-1 shrink-0"
                            :class="index < activeStep ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700'"></div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">

        {{-- STEP 1: EMPRESA --}}
        @can('settings.company')
        <div x-show="activeStep === 0" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="p-6 space-y-6" x-data="companyForm()">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">1</div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Mi Taller</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Información de tu negocio que aparece en comprobantes</p>
                    </div>
                </div>

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
                            {{-- Logo actual --}}
                            <template x-if="!logoPreview">
                                <div class="w-24 h-24 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                                    @if($tenant->logo)
                                    <img src="{{ route('r2.serve', ['path' => $tenant->logo]) }}" alt="Logo" class="w-full h-full object-contain rounded-lg">
                                    @else
                                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" /></svg>
                                    @endif
                                </div>
                            </template>
                            {{-- Preview del nuevo logo --}}
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

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Configuración de Correo SMTP</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Configura tu propio servidor SMTP para enviar notificaciones a tus clientes.
                        Estos datos son responsabilidad de tu empresa.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="mail_host" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Servidor SMTP</label>
                            <input type="text" name="mail_host" id="mail_host" value="{{ old('mail_host', $tenant->mail_host) }}"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"
                                placeholder="smtp.gmail.com">
                        </div>

                        <div>
                            <label for="mail_port" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Puerto</label>
                            <input type="text" name="mail_port" id="mail_port" value="{{ old('mail_port', $tenant->mail_port) }}"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"
                                placeholder="587">
                        </div>

                        <div>
                            <label for="mail_username" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Usuario</label>
                            <input type="text" name="mail_username" id="mail_username" value="{{ old('mail_username', $tenant->mail_username) }}"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"
                                placeholder="tu@correo.com">
                        </div>

                        <div>
                            <label for="mail_password" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Contraseña</label>
                            <input type="password" name="mail_password" id="mail_password" value="{{ old('mail_password', $tenant->mail_password ? '********' : '') }}"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"
                                placeholder="••••••••">
                            <p class="mt-1 text-xs text-gray-400">Déjalo en blanco para mantener la actual.</p>
                        </div>

                        <div>
                            <label for="mail_encryption" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Encriptación</label>
                            <select name="mail_encryption" id="mail_encryption"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                                <option value="tls" {{ ($tenant->mail_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ $tenant->mail_encryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ $tenant->mail_encryption === '' ? 'selected' : '' }}>Ninguna</option>
                            </select>
                        </div>

                        <div>
                            <label for="mail_from_address" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Correo desde</label>
                            <input type="email" name="mail_from_address" id="mail_from_address" value="{{ old('mail_from_address', $tenant->mail_from_address) }}"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"
                                placeholder="notificaciones@tudominio.com">
                        </div>

                        <div>
                            <label for="mail_from_name" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Nombre desde</label>
                            <input type="text" name="mail_from_name" id="mail_from_name" value="{{ old('mail_from_name', $tenant->mail_from_name) }}"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"
                                placeholder="{{ $tenant->name }}">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="activeStep = 1" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Siguiente →
                    </button>
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        💾 Guardar
                    </button>
                </div>
            </form>
        </div>
        @endcan

        {{-- STEP 2: USUARIOS --}}
        @can('settings.users')
        <div x-show="activeStep === 1" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">2</div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Tu Equipo</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Gestiona las cuentas de tu equipo de trabajo</p>
                    </div>
                    <button @click="showCreateUser = true"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        👤 Nuevo usuario
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @foreach($user->roles as $role)
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                        {{ $user->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' }}">
                                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->roles->first()?->name }}', {{ $user->is_active ? 'true' : 'false' }})"
                                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 mr-3">Editar</button>
                                    @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('settings.users.destroy', $user) }}" class="inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-500">Eliminar</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No hay usuarios registrados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="activeStep = 0" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        ← Anterior
                    </button>
                    <button type="button" @click="activeStep = 2" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Siguiente →
                    </button>
                </div>
            </div>
        </div>
        @endcan

        {{-- STEP 3: ROLES --}}
        @can('settings.roles')
        <div x-show="activeStep === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">3</div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Roles y Permisos</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Define roles y asigna permisos granulares</p>
                    </div>
                    <button @click="showCreateRole = true"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        🛡️ Nuevo rol
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    @foreach($roles as $role)
                    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="p-5">
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
                                <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                                    @if($role->name !== 'super-admin' && $role->name !== 'admin')
                                    <form action="{{ route('settings.roles.destroy', $role) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar el rol &quot;{{ $role->name }}&quot;? Los usuarios con este rol quedarán sin rol asignado.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500 text-sm font-medium">Eliminar</button>
                                    </form>
                                    @else
                                    <div></div>
                                    @endif
                                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                        Guardar permisos
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="flex justify-between pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="activeStep = 1" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        ← Anterior
                    </button>
                    <button type="button" @click="activeStep = 3" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Siguiente →
                    </button>
                </div>
            </div>
        </div>
        @endcan

        {{-- STEP 4: IMPUESTOS --}}
        @can('settings.taxes')
        <div x-show="activeStep === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <form method="POST" action="{{ route('settings.taxes.update') }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">4</div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Impuestos y Formato</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Configuración de impuestos, formato de impresión y numeración</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Impuestos</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="hidden" name="tax_enabled" value="0">
                            <input type="checkbox" name="tax_enabled" id="tax_enabled" value="1" {{ old('tax_enabled', $tenant->tax_enabled) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                            <label for="tax_enabled" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Aplicar impuestos en ventas y cotizaciones</label>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="tax_percentage" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Porcentaje de impuesto por defecto (%)</label>
                                <input type="number" name="tax_percentage" id="tax_percentage" value="{{ old('tax_percentage', $tenant->tax_percentage) }}" step="0.01" min="0" max="100"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            </div>

                            <div>
                                <label for="tax_mode" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Modo de cálculo</label>
                                <select name="tax_mode" id="tax_mode"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                                    <option value="per_item" {{ old('tax_mode', $tenant->tax_mode) === 'per_item' ? 'selected' : '' }}>Por producto/servicio individual</option>
                                    <option value="on_total" {{ old('tax_mode', $tenant->tax_mode) === 'on_total' ? 'selected' : '' }}>Sobre el total de la venta</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Formato de Impresión</h3>
                    <div>
                        <label for="print_format" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Formato preferido</label>
                        <select name="print_format" id="print_format"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <option value="ticket_58mm" {{ old('print_format', $tenant->print_format) === 'ticket_58mm' ? 'selected' : '' }}>Ticket térmico 58mm</option>
                            <option value="ticket_80mm" {{ old('print_format', $tenant->print_format) === 'ticket_80mm' ? 'selected' : '' }}>Ticket térmico 80mm</option>
                            <option value="a4" {{ old('print_format', $tenant->print_format) === 'a4' ? 'selected' : '' }}>Hoja A4</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Numeración de Órdenes</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="work_order_prefix" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Prefijo</label>
                            <input type="text" name="work_order_prefix" id="work_order_prefix" value="{{ old('work_order_prefix', $tenant->work_order_prefix) }}"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            <p class="mt-1 text-xs text-gray-500">Ej: OT-, REP-, ORD-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Siguiente número</label>
                            <input type="text" value="{{ $tenant->work_order_prefix }}{{ str_pad($tenant->work_order_sequence + 1, 5, '0', STR_PAD_LEFT) }}" disabled
                                class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-500 dark:text-gray-400 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 bg-gray-50 dark:bg-gray-600 sm:text-sm sm:leading-6">
                            <p class="mt-1 text-xs text-gray-500">Se incrementa automáticamente con cada orden</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="activeStep = 2" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        ← Anterior
                    </button>
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        💾 Guardar
                    </button>
                </div>
            </form>
        </div>
        @endcan

        {{-- STEP 5: CLAUSULAS --}}
        @can('settings.clauses')
        <div x-show="activeStep === 4" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">5</div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Cláusulas y Políticas</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Términos y condiciones que se imprimen en los comprobantes</p>
                    </div>
                    <button @click="showCreateClause = true"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        📄 Nueva cláusula
                    </button>
                </div>

                <div class="space-y-4">
                    @forelse($clauses as $clause)
                    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="p-5">
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
                                    <button @click="editClause({{ $clause->id }}, '{{ addslashes($clause->title) }}', '{{ addslashes($clause->content) }}', '{{ $clause->type }}', {{ $clause->is_active ? 'true' : 'false' }}, {{ $clause->print_on_receipt ? 'true' : 'false' }})"
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
                    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl p-12 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No hay cláusulas registradas. Crea la primera para que aparezca en los comprobantes.</p>
                    </div>
                    @endforelse
                </div>

                <div class="flex justify-between pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="activeStep = 3" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        ← Anterior
                    </button>
                    <button type="button" @click="activeStep = 0" class="inline-flex items-center rounded-md bg-green-100 dark:bg-green-900/30 px-4 py-2 text-sm font-semibold text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                        ✓ Completado
                    </button>
                </div>
            </div>
        </div>
        @endcan
    </div>

        {{-- STEP 6: NOTIFICACIONES (solo lectura para el tenant) --}}
        @can('settings.company')
        <div x-show="activeStep === 5" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="p-6 space-y-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">📱 Notificaciones por WhatsApp</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Conecta tu número y envía notificaciones automáticas a tus clientes</p>
                        </div>
                    </div>
                    <a href="{{ route('whatsapp.config') }}"
                       class="inline-flex items-center rounded-md shrink-0 {{ $tenant->hasFeature('notifications_whatsapp') ? 'bg-green-600 hover:bg-green-500' : 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed' }} px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors">
                        {{ $tenant->hasFeature('notifications_whatsapp') ? 'Configurar WhatsApp' : 'Solo plan Premium' }}
                    </a>
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">6</div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Plantillas de Notificación</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Mensajes predefinidos que se envían a los clientes vía WhatsApp.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @php
                        $events = [
                            'order_created' => 'Orden creada',
                            'diagnosis_completed' => 'Diagnóstico completado',
                            'quote_sent' => 'Cotización enviada',
                            'quote_approved' => 'Cotización aprobada',
                            'quote_rejected' => 'Cotización rechazada',
                            'repair_completed' => 'Reparación completada',
                            'ready_for_pickup' => 'Listo para recoger',
                        ];
                        $defaults = \Database\Seeders\NotificationTemplateSeeder::getDefaults();
                    @endphp

                    @foreach($events as $eventKey => $eventLabel)
                        @php
                            $template = $templates->firstWhere(fn($t) => $t->event === $eventKey && $t->channel === 'whatsapp');
                            $raw = $template?->body ?? ($defaults[$eventKey]['whatsapp']['body'] ?? '');
                            $preview = str_replace(
                                ['{client_name}', '{work_order_number}', '{tracking_url}'],
                                ['Juan Pérez', 'W001234', 'repcellpos.com/seguimiento/abc123'],
                                $raw
                            );
                        @endphp
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400">
                                    {{ $eventLabel }}
                                </span>
                                @if($template?->is_active)
                                    <span class="text-xs text-green-600 dark:text-green-400 font-medium">● Activa</span>
                                @endif
                            </div>
                            <div class="flex justify-end">
                                <div class="bg-[#dcf8c6] dark:bg-gray-600 rounded-lg rounded-tr-sm px-4 py-3 max-w-xs shadow-sm">
                                    <p class="text-sm text-gray-900 dark:text-gray-100 leading-relaxed whitespace-pre-wrap">{{ $preview }}</p>
                                    <div class="flex items-center justify-end gap-1 mt-1.5">
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400">12:34</span>
                                        <svg class="w-3.5 h-3.5 text-green-500" viewBox="0 0 16 11" fill="currentColor"><path d="M11.071.653a.457.457 0 00-.304-.102.493.493 0 00-.381.178l-6.19 7.636-2.011-2.095a.463.463 0 00-.336-.153.454.454 0 00-.33.154.515.515 0 00-.136.348.482.482 0 00.144.342l2.358 2.455a.465.465 0 00.33.153.472.472 0 00.33-.153L11.2 1.346a.485.485 0 00.14-.347.5.5 0 00-.14-.346.607.607 0 00-.129.01zM8.77 7.3l-1.118 1.34c.353.186.745.298 1.162.298.648 0 1.236-.247 1.687-.648L8.77 7.3z"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="activeStep = 4" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        ← Anterior
                    </button>
                    <button type="button" @click="activeStep = 0" class="inline-flex items-center rounded-md bg-green-100 dark:bg-green-900/30 px-4 py-2 text-sm font-semibold text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                        ✓ Completado
                    </button>
                </div>
            </div>
        </div>
        @endcan

    {{-- MODAL: Create User --}}
    <div x-show="showCreateUser" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateUser = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Nuevo Usuario</h3>
                <form method="POST" action="{{ route('settings.users.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Contraseña <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Rol <span class="text-red-500">*</span></label>
                        <select name="role" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showCreateUser = false" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Crear usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Edit User --}}
    <div x-show="showEditUser" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showEditUser = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Editar Usuario</h3>
                <form method="POST" x-bind:action="`/settings/users/${editUserData.id}`" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="editUserData.name" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Rol <span class="text-red-500">*</span></label>
                        <select name="role" x-model="editUserData.role" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" x-model="editUserData.isActive"
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                        <label class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Activo</label>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showEditUser = false" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Create Role --}}
    <div x-show="showCreateRole" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateRole = false"></div>
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
                        <button type="button" @click="showCreateRole = false" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Crear rol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Create Clause --}}
    <div x-show="showCreateClause" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateClause = false"></div>
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
                        <button type="button" @click="showCreateClause = false" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Crear cláusula</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Edit Clause --}}
    <div x-show="showEditClause" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showEditClause = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Editar Cláusula</h3>
                <form method="POST" x-bind:action="`/settings/clauses/${editClauseData.id}`" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Título <span class="text-red-500">*</span></label>
                        <input type="text" name="title" x-model="editClauseData.title" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Tipo <span class="text-red-500">*</span></label>
                        <select name="type" x-model="editClauseData.type" required
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
                        <textarea name="content" x-model="editClauseData.content" rows="6" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" x-model="editClauseData.isActive"
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                            <label class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Activa</label>
                        </div>
                        <div class="flex items-center">
                            <input type="hidden" name="print_on_receipt" value="0">
                            <input type="checkbox" name="print_on_receipt" value="1" x-model="editClauseData.printOnReceipt"
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-600 dark:bg-gray-700">
                            <label class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Imprimir en comprobantes</label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showEditClause = false" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function settingsApp() {
    return {
        steps: ['Mi Taller', 'Tu Equipo', 'Roles', 'Impuestos', 'Cláusulas', 'Notificaciones'],
        activeStep: 0,
        showCreateUser: false,
        showEditUser: false,
        editUserData: { id: 0, name: '', email: '', role: '', isActive: false },
        showCreateRole: false,
        showCreateClause: false,
        showEditClause: false,
        editClauseData: { id: 0, title: '', content: '', type: 'terms', isActive: true, printOnReceipt: false },
        editUser(id, name, email, role, isActive) {
            this.editUserData = { id, name, email, role, isActive };
            this.showEditUser = true;
        },
        editClause(id, title, content, type, isActive, printOnReceipt) {
            this.editClauseData = { id, title, content, type, isActive, printOnReceipt };
            this.showEditClause = true;
        },
    }
}

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

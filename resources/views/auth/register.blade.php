<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta — RepCellPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <a href="{{ url('/') }}" class="flex items-center justify-center gap-2">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                </div>
                <span class="text-xl font-bold text-gray-900">RepCell<span class="text-indigo-600">POS</span></span>
            </a>
            <h2 class="mt-6 text-center text-2xl font-bold tracking-tight text-gray-900">
                Crea tu cuenta
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Registra tu taller y comienza a gestionar reparaciones
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white px-6 py-8 shadow-sm sm:rounded-xl sm:px-12 ring-1 ring-gray-200">
                @if($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 p-4">
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-700">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form class="space-y-6" method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Datos del taller</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="business_name" class="block text-sm font-medium text-gray-900">Nombre del taller <span class="text-red-500">*</span></label>
                                <input type="text" name="business_name" id="business_name" value="{{ old('business_name') }}" required placeholder="Ej: Celulares Express"
                                    class="mt-1 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            <div>
                                <label for="business_phone" class="block text-sm font-medium text-gray-900">Teléfono del taller <span class="text-red-500">*</span></label>
                                <input type="text" name="business_phone" id="business_phone" value="{{ old('business_phone') }}" required placeholder="Ej: +52 55 1234 5678"
                                    class="mt-1 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Tu cuenta de administrador</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="admin_name" class="block text-sm font-medium text-gray-900">Tu nombre completo <span class="text-red-500">*</span></label>
                                <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}" required placeholder="Ej: Juan Pérez"
                                    class="mt-1 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            <div>
                                <label for="admin_email" class="block text-sm font-medium text-gray-900">Correo electrónico <span class="text-red-500">*</span></label>
                                <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}" required placeholder="tu@email.com"
                                    class="mt-1 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            <div>
                                <label for="admin_password" class="block text-sm font-medium text-gray-900">Contraseña <span class="text-red-500">*</span></label>
                                <input type="password" name="admin_password" id="admin_password" required minlength="8"
                                    class="mt-1 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres</p>
                            </div>
                            <div>
                                <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-900">Confirmar contraseña <span class="text-red-500">*</span></label>
                                <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required
                                    class="mt-1 block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="flex w-full justify-center rounded-lg bg-indigo-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                            Crear mi taller y comenzar
                        </button>
                    </div>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Iniciar sesión</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

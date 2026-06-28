<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Correo verificado — RepCellPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <div class="min-h-full flex flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">¡Correo verificado!</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Tu dirección de correo electrónico ha sido verificada exitosamente.
                </p>
            </div>

            @auth
                <a href="{{ $dashboardRoute }}"
                   class="block w-full text-center rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    Ir al panel de control
                </a>
                <p class="mt-3 text-center text-xs text-gray-500">
                    Serás redirigido automáticamente en <span id="countdown" class="font-semibold text-indigo-600">5</span> segundos
                </p>
            @else
                <a href="{{ route('login') }}"
                   class="block w-full text-center rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    Iniciar sesión
                </a>
            @endauth
        </div>

        <p class="mt-12 text-xs text-gray-400">
            &copy; {{ date('Y') }} RepCellPOS. Todos los derechos reservados.
        </p>
    </div>

    @auth
    <script>
        let seconds = 5;
        setInterval(() => {
            seconds--;
            document.getElementById('countdown').textContent = seconds;
            if (seconds <= 0) {
                window.location.href = '{{ $dashboardRoute }}';
            }
        }, 1000);
    </script>
    @endauth
</body>
</html>

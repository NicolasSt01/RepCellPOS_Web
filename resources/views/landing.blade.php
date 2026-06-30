<!DOCTYPE html>
<html lang="es-MX" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RepCellPOS — Sistema para Taller de Celulares | Gestión, POS e Inventario</title>
    <meta name="description" content="Sistema de gestión para talleres de reparación de celulares en México. Órdenes de trabajo, inventario con kardex, punto de venta, control de caja, cotizaciones y notificaciones WhatsApp. Transforma la administración de tu taller.">
    <meta name="keywords" content="sistema para taller de celulares, software para reparación de celulares, gestión de taller de reparación móvil, POS para taller de celulares, control de inventario para taller, órdenes de trabajo taller celular, administración de taller México, programa para taller de reparación">
    <meta name="author" content="RepCellPOS">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">

    <meta property="og:title" content="RepCellPOS — El sistema que tu taller de celulares necesita">
    <meta property="og:description" content="Gestiona órdenes de trabajo, inventario, ventas y caja desde un solo lugar. Tus clientes siguen su reparación en tiempo real.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:locale" content="es_MX">
    <meta property="og:site_name" content="RepCellPOS">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="RepCellPOS — Sistema para Taller de Celulares">
    <meta name="twitter:description" content="Gestiona órdenes de trabajo, inventario, ventas y caja desde un solo lugar. Diseñado para talleres de reparación celular en México.">

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "RepCellPOS",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Sistema integral de gestión para talleres de reparación de celulares. Órdenes de trabajo, inventario, POS, control de caja, cotizaciones y seguimiento público.",
        "offers": {
            "@type": "Offer",
            "priceCurrency": "MXN",
            "price": "0",
            "description": "Plan gratuito disponible. Planes profesionales desde $399/mes"
        },
        "author": {
            "@type": "Organization",
            "name": "RepCellPOS"
        }
    }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white antialiased text-gray-900 font-sans">

    {{-- NAVBAR --}}
    <header class="fixed top-0 inset-x-0 z-50 bg-white/95 backdrop-blur border-b border-gray-100">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="/" class="flex items-center gap-2.5">
                    <img src="{{ asset('logo.png') }}" alt="RepCellPOS" class="w-8 h-8 rounded-lg object-cover flex-shrink-0">
                    <span class="text-xl font-bold text-gray-900 tracking-tight">RepCell<span class="text-indigo-600">POS</span></span>
                </a>

                <nav class="hidden md:flex items-center gap-8">
                    <a href="#funcionalidades" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Funcionalidades</a>
                    <a href="#como-funciona" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Cómo funciona</a>
                    <a href="#precios" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Precios</a>
                    <a href="#faq" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">FAQ</a>
                </nav>

                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-700 hover:text-gray-900 transition-colors">Iniciar sesión</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 active:bg-indigo-700 transition-colors">
                        Crear taller gratis
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- HERO --}}
    <section class="relative pt-32 pb-20 sm:pt-40 sm:pb-28 overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 border border-indigo-100 px-4 py-1.5 text-sm font-medium text-indigo-700 mb-8">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    El sistema que usan talleres en todo México
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-[1.1]">
                    Olvídate de las libretas.
                    <br>
                    <span class="text-indigo-600">Digitaliza tu taller</span> y hazlo crecer.
                </h1>

                <p class="mt-6 text-lg sm:text-xl text-gray-600 leading-relaxed max-w-2xl mx-auto">
                    El sistema todo-en-uno para talleres de reparación de celulares: órdenes de trabajo, inventario, 
                    punto de venta, control de caja, cotizaciones y notificaciones automáticas por WhatsApp. 
                    Todo lo que necesitas para administrar tu taller como un profesional.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-indigo-600 px-8 py-3.5 text-base font-semibold text-white shadow-md hover:bg-indigo-500 active:bg-indigo-700 transition-colors">
                        Crear mi taller gratis
                        <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="#funcionalidades" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-white px-8 py-3.5 text-base font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50 transition-colors">
                        Ver funcionalidades
                    </a>
                </div>

                <p class="mt-6 text-sm text-gray-400">Sin tarjeta de crédito. Configuración en 2 minutos. Prueba gratuita de 7 días.</p>
            </div>
        </div>
    </section>

    {{-- PAIN → SOLUTION / STATS --}}
    <section class="border-t border-gray-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <p class="text-3xl sm:text-4xl font-extrabold text-indigo-600">12+</p>
                    <p class="mt-1.5 text-sm text-gray-500">Módulos integrados</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl sm:text-4xl font-extrabold text-indigo-600">0</p>
                    <p class="mt-1.5 text-sm text-gray-500">Libretas ni papeles</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl sm:text-4xl font-extrabold text-indigo-600">7 días</p>
                    <p class="mt-1.5 text-sm text-gray-500">Prueba gratuita</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl sm:text-4xl font-extrabold text-indigo-600">100%</p>
                    <p class="mt-1.5 text-sm text-gray-500">En línea — desde cualquier lugar</p>
                </div>
            </div>
        </div>
    </section>

    {{-- PROBLEM → SOLUTION --}}
    <section class="border-t border-gray-100 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">
                    ¿Cansado de perder clientes por no dar seguimiento?
                </h2>
                <p class="mt-4 text-lg text-gray-600">
                    Sabemos cómo funciona un taller de reparación porque lo vivimos. 
                    Deja atrás el desorden y dale a tu negocio la herramienta que merece.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <div class="bg-white rounded-xl p-6 ring-1 ring-gray-200">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Antes: el caos de siempre</p>
                            <ul class="mt-2 space-y-2 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <span class="text-red-400 mt-0.5">✕</span>
                                    Órdenes de trabajo en hojas sueltas que se pierden
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-red-400 mt-0.5">✕</span>
                                    Clientes llamando cada hora para saber el estado
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-red-400 mt-0.5">✕</span>
                                    Inventario descontrolado: no sabes qué refacciones tienes
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-red-400 mt-0.5">✕</span>
                                    Ventas sin registro, caja que no cuadra
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 ring-1 ring-gray-200">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Con RepCellPOS: control total</p>
                            <ul class="mt-2 space-y-2 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    Órdenes digitales con timeline y seguimiento
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    Clientes reciben notificaciones automáticas por WhatsApp
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    Inventario con kardex y alertas de stock bajo
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    POS, control de caja y reportes en tiempo real
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section id="funcionalidades" class="border-t border-gray-100 bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">Funcionalidades</p>
                <h2 class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">Todo lo que tu taller necesita en un solo sistema</h2>
                <p class="mt-4 text-lg text-gray-600">Diseñado específicamente para talleres de reparación de celulares en México. Cada función pensada para hacer tu trabajo más fácil.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
                {{-- 1 --}}
                <div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.384 3.065A1 1 0 014.5 17.37V6.63a1 1 0 011.536-.864l5.384 3.065M15.42 15.17l5.384-3.065a1 1 0 000-1.732L15.42 7.31" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Órdenes de Trabajo</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Ciclo completo desde que recibes el equipo hasta que lo entregas. Timeline con cada cambio de estado, prioridades, asignación a técnicos y anotaciones.</p>
                </div>

                {{-- 2 --}}
                <div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Inventario con Kardex</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Controla tus refacciones con trazabilidad completa. Alertas cuando el stock está bajo. Categoriza por marca y modelo para encontrar rápido.</p>
                </div>

                {{-- 3 --}}
                <div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Punto de Venta (POS)</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Vende productos, cobra reparaciones y cotizaciones. Soporta efectivo, tarjeta/transferencia y pago mixto. Cálculo automático de cambio e impuestos.</p>
                </div>

                {{-- 4 --}}
                <div>
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 3h.008v.008H8.25v-.008zm0 3h.008v.008H8.25v-.008zm3-3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm3-3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zM21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Control de Caja</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Apertura y cierre de caja por turno. Reportes desglosados por método de pago, retiros autorizados y detección de diferencias al cierre.</p>
                </div>

                {{-- 5 --}}
                <div>
                    <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Notificaciones por WhatsApp</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Tus clientes reciben mensajes automáticos en cada etapa: diagnóstico listo, cotización enviada, reparación terminada. Sin que tú levantes un dedo.</p>
                </div>

                {{-- 6 --}}
                <div>
                    <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Portal de Seguimiento para Clientes</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Cada cliente recibe un link único para ver el estado de su reparación en tiempo real. Sin app, sin registro. Menos llamadas, más confianza.</p>
                </div>

                {{-- 7 --}}
                <div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Cotizaciones</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Genera presupuestos detallados con refacciones y mano de obra. Envía la cotización por WhatsApp, el cliente aprueba o rechaza desde su celular.</p>
                </div>

                {{-- 8 --}}
                <div>
                    <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Reportes Avanzados</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Sabes exactamente qué servicios dan más ganancia, qué técnico rinde más, qué productos se venden más y cómo va tu negocio mes con mes.</p>
                </div>

                {{-- 9 --}}
                <div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Roles y Permisos</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Cada quien ve lo que necesita: el técnico solo las reparaciones, el secretario el POS, el administrador todo. Tú decides los permisos.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- WHATSAPP VALUE --}}
    <section class="border-t border-gray-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
            <div class="max-w-4xl mx-auto">
                <div class="bg-gray-50 rounded-2xl p-8 sm:p-12 ring-1 ring-gray-200">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center gap-8">
                        <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">WhatsApp integrado — Tus clientes siempre informados</h3>
                            <p class="mt-2 text-gray-600 leading-relaxed">
                                Conecta tu número de WhatsApp y olvídate de estar marcando uno por uno. Cuando el diagnóstico 
                                esté listo, cuando la reparación termine o cuando la cotización esté enviada, el cliente recibe 
                                un mensaje automático con el enlace para dar seguimiento. Sin hacer nada.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section id="como-funciona" class="border-t border-gray-100 bg-gray-50 py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">Cómo funciona</p>
                <h2 class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">De la recepción a la entrega en 4 pasos</h2>
                <p class="mt-4 text-lg text-gray-600">El flujo de trabajo más natural para tu taller, ahora digitalizado.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 max-w-5xl mx-auto">
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <span class="text-white text-xl font-bold">1</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-gray-900">Recibe el equipo</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Registra el dispositivo, captura fotos del estado actual y genera el comprobante para el cliente al instante.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <span class="text-white text-xl font-bold">2</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-gray-900">Diagnostica y cotiza</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">El técnico revisa, selecciona refacciones del inventario y envía la cotización por WhatsApp automáticamente.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <span class="text-white text-xl font-bold">3</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-gray-900">Repara</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">El cliente aprueba desde su celular, el técnico repara y el inventario se descuenta automáticamente.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <span class="text-white text-xl font-bold">4</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-gray-900">Cobra y entrega</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Cobra desde el POS, el cliente recibe su comprobante por WhatsApp y el equipo queda liberado.</p>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    Probar gratis por 7 días
                    <svg class="ml-2 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- DIFFERENTIATORS --}}
    <section class="border-t border-gray-100 bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">Por qué RepCellPOS</p>
                <h2 class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">Hecho por técnicos, para técnicos</h2>
                <p class="mt-4 text-lg text-gray-600">No somos un sistema genérico. Cada funcionalidad está pensada para resolver los problemas reales de un taller de reparación de celulares.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div class="flex gap-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Sin instalación</h3>
                        <p class="mt-1 text-sm text-gray-600">Funciona en cualquier navegador. Celular, tablet o computadora. No necesitas instalar nada.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Datos seguros en la nube</h3>
                        <p class="mt-1 text-sm text-gray-600">Tu información respaldada y accesible desde cualquier lugar. Olvídate de perder datos por un disco duro dañado.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Soporte en español</h3>
                        <p class="mt-1 text-sm text-gray-600">Equipo de soporte local. Resolvemos tus dudas rápido, en tu idioma y en tu horario.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Portal público para clientes</h3>
                        <p class="mt-1 text-sm text-gray-600">Cada cliente recibe un enlace para ver el progreso de su reparación. Menos llamadas, más confianza y transparencia.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Roles y permisos personalizables</h3>
                        <p class="mt-1 text-sm text-gray-600">Crea los roles que necesites: técnico, secretario, administrador. Cada quien con acceso solo a lo que necesita.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Actualizaciones constantes</h3>
                        <p class="mt-1 text-sm text-gray-600">Mejoramos el sistema cada semana con nuevas funcionalidades basadas en lo que los talleres como el tuyo nos piden.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PRICING --}}
    <section id="precios" class="border-t border-gray-100 bg-gray-50 py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">Precios</p>
                <h2 class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">Planes para todo tipo de taller</h2>
                <p class="mt-4 text-lg text-gray-600">Desde el taller que empieza hasta el que ya tiene varios técnicos. Todos los planes incluyen 7 días de prueba gratuita.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                @foreach($plans as $plan)
                    <div class="relative bg-white rounded-2xl ring-1 p-8 flex flex-col @if($plan->is_highlight) ring-2 ring-indigo-600 shadow-lg @else ring-gray-200 shadow-sm @endif">
                        @if($plan->is_highlight)
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                                <span class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white">Más popular</span>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h3>
                            @if($plan->description)
                                <p class="mt-1 text-sm text-gray-500">{{ $plan->description }}</p>
                            @endif
                            <div class="mt-4 flex items-baseline gap-1">
                                <span class="text-4xl font-extrabold text-gray-900">${{ number_format($plan->price) }}</span>
                                <span class="text-lg text-gray-500">/mes</span>
                            </div>
                        </div>

                        <ul class="mt-6 space-y-3 flex-1">
                            @foreach($plan->features as $feature => $enabled)
                                <li class="flex items-center gap-3 text-sm {{ $enabled ? 'text-gray-700' : 'text-gray-400' }}">
                                    @if($enabled)
                                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    @endif
                                    @switch($feature)
                                        @case('work_orders') Órdenes de Trabajo @break
                                        @case('quotes') Cotizaciones @break
                                        @case('pos') Punto de Venta (POS) @break
                                        @case('notifications_email') Notificaciones Email @break
                                        @case('notifications_whatsapp') Notificaciones WhatsApp @break
                                        @case('notifications_low_stock') Alertas de Stock Bajo @break
                                        @case('reports_advanced') Reportes Avanzados @break
                                        @default {{ $feature }} @break
                                    @endswitch
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Límites del plan</p>
                            <ul class="space-y-1 text-xs text-gray-500">
                                @foreach($plan->limits as $limit => $value)
                                    <li class="flex items-center gap-2">
                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                        @switch($limit)
                                            @case('max_users') Usuarios: {{ $value === -1 ? 'Ilimitados' : $value }} @break
                                            @case('max_clients') Clientes: {{ $value === -1 ? 'Ilimitados' : $value }} @break
                                            @case('max_monthly_work_orders') OT/mes: {{ $value === -1 ? 'Ilimitadas' : $value }} @break
                                            @case('storage_mb') Almacenamiento: {{ $value >= 1000 ? ($value / 1000).' GB' : $value.' MB' }} @break
                                            @default {{ $limit }}: {{ $value === -1 ? 'Ilimitado' : $value }} @break
                                        @endswitch
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <a href="{{ route('register') }}" class="mt-6 block w-full text-center rounded-lg py-3 text-base font-semibold transition-colors @if($plan->is_highlight) bg-indigo-600 text-white shadow-sm hover:bg-indigo-500 @else bg-white text-gray-700 ring-1 ring-gray-300 hover:ring-gray-400 hover:bg-gray-50 @endif">
                            Comenzar prueba gratis
                        </a>
                    </div>
                @endforeach
            </div>

            <p class="mt-10 text-center text-sm text-gray-500">Todos los planes incluyen 7 días de prueba gratuita. Sin tarjeta de crédito. Cancela cuando quieras.</p>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="border-t border-gray-100 bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest">FAQ</p>
                <h2 class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">Preguntas frecuentes</h2>
            </div>

            <div class="space-y-6">
                <details class="group bg-gray-50 rounded-xl p-6 open:ring-1 open:ring-indigo-100">
                    <summary class="flex items-center justify-between cursor-pointer list-none">
                        <span class="font-semibold text-gray-900">¿Necesito instalar algo en mi computadora?</span>
                        <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </summary>
                    <p class="mt-4 text-sm text-gray-600 leading-relaxed">No. RepCellPOS funciona completamente en línea, solo necesitas un navegador web (Chrome, Safari, Edge) y conexión a internet. Puedes usarlo desde tu computadora, tablet o celular.</p>
                </details>

                <details class="group bg-gray-50 rounded-xl p-6 open:ring-1 open:ring-indigo-100">
                    <summary class="flex items-center justify-between cursor-pointer list-none">
                        <span class="font-semibold text-gray-900">¿Puedo usar mi propio número de WhatsApp?</span>
                        <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </summary>
                    <p class="mt-4 text-sm text-gray-600 leading-relaxed">Sí. Puedes conectar tu número de WhatsApp directo al sistema. Las notificaciones se enviarán automáticamente desde tu propio número. El proceso es sencillo: escaneas un código QR y listo.</p>
                </details>

                <details class="group bg-gray-50 rounded-xl p-6 open:ring-1 open:ring-indigo-100">
                    <summary class="flex items-center justify-between cursor-pointer list-none">
                        <span class="font-semibold text-gray-900">¿Puedo probarlo antes de pagar?</span>
                        <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </summary>
                    <p class="mt-4 text-sm text-gray-600 leading-relaxed">Claro. Todos los planes incluyen 7 días de prueba gratuita sin necesidad de registrar tarjeta de crédito. Puedes explorar todas las funcionalidades y si no te convence, simplemente no continúas.</p>
                </details>

                <details class="group bg-gray-50 rounded-xl p-6 open:ring-1 open:ring-indigo-100">
                    <summary class="flex items-center justify-between cursor-pointer list-none">
                        <span class="font-semibold text-gray-900">¿Puedo tener varios técnicos usando el sistema?</span>
                        <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </summary>
                    <p class="mt-4 text-sm text-gray-600 leading-relaxed">Sí. Dependiendo del plan que elijas, puedes agregar desde 1 hasta usuarios ilimitados. Cada usuario tiene su propio acceso y puedes asignar permisos específicos (quién puede vender, quién puede reparar, quién puede ver reportes, etc.).</p>
                </details>

                <details class="group bg-gray-50 rounded-xl p-6 open:ring-1 open:ring-indigo-100">
                    <summary class="flex items-center justify-between cursor-pointer list-none">
                        <span class="font-semibold text-gray-900">¿Cómo se manejan las cotizaciones?</span>
                        <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </summary>
                    <p class="mt-4 text-sm text-gray-600 leading-relaxed">El técnico selecciona las refacciones necesarias del inventario, agrega la mano de obra y el sistema calcula automáticamente el total. La cotización se envía por WhatsApp al cliente, quien puede aprobar o rechazar desde su celular sin necesidad de llamar.</p>
                </details>

                <details class="group bg-gray-50 rounded-xl p-6 open:ring-1 open:ring-indigo-100">
                    <summary class="flex items-center justify-between cursor-pointer list-none">
                        <span class="font-semibold text-gray-900">¿Puedo imprimir tickets y comprobantes?</span>
                        <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </summary>
                    <p class="mt-4 text-sm text-gray-600 leading-relaxed">Sí. El sistema genera comprobantes en formato ticket térmico (58mm y 80mm) y hoja A4. Puedes imprimir directamente a tu impresora térmica o convencional. También enviamos copia por WhatsApp y email.</p>
                </details>
            </div>
        </div>
    </section>

    {{-- FINAL CTA --}}
    <section class="border-t border-gray-100 bg-gray-900">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">¿Listo para llevar tu taller al siguiente nivel?</h2>
            <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">Deja las libretas atrás. Digitaliza tus operaciones y da a tus clientes la experiencia que merecen.</p>
            <a href="{{ route('register') }}" class="mt-8 inline-flex items-center rounded-lg bg-indigo-600 px-8 py-3.5 text-base font-semibold text-white shadow-md hover:bg-indigo-500 active:bg-indigo-700 transition-colors">
                Crear mi taller gratis
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
            <p class="mt-4 text-sm text-gray-400">7 días de prueba gratuita. Sin tarjeta de crédito. Configuración en 2 minutos.</p>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-gray-950 border-t border-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="sm:col-span-2 lg:col-span-1">
                    <a href="/" class="flex items-center gap-2.5">
                        <img src="{{ asset('logo.png') }}" alt="RepCellPOS" class="w-8 h-8 rounded-lg object-cover flex-shrink-0">
                        <span class="text-lg font-bold text-white">RepCell<span class="text-indigo-400">POS</span></span>
                    </a>
                    <p class="mt-3 text-sm text-gray-400 leading-relaxed">
                        Sistema de gestión para talleres de reparación de celulares. Hecho en México para técnicos mexicanos.
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Producto</p>
                    <ul class="mt-4 space-y-3">
                        <li><a href="#funcionalidades" class="text-sm text-gray-300 hover:text-white transition-colors">Funcionalidades</a></li>
                        <li><a href="#precios" class="text-sm text-gray-300 hover:text-white transition-colors">Planes y precios</a></li>
                        <li><a href="{{ route('register') }}" class="text-sm text-gray-300 hover:text-white transition-colors">Crear cuenta</a></li>
                        <li><a href="{{ route('login') }}" class="text-sm text-gray-300 hover:text-white transition-colors">Iniciar sesión</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Recursos</p>
                    <ul class="mt-4 space-y-3">
                        <li><a href="#como-funciona" class="text-sm text-gray-300 hover:text-white transition-colors">Cómo funciona</a></li>
                        <li><a href="#faq" class="text-sm text-gray-300 hover:text-white transition-colors">Preguntas frecuentes</a></li>
                        <li><a href="{{ route('tracking.show', ['token' => 'demo']) }}" class="text-sm text-gray-300 hover:text-white transition-colors">Demo: seguimiento público</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Empresa</p>
                    <ul class="mt-4 space-y-3">
                        <li><span class="text-sm text-gray-300">contacto@repcellpos.com</span></li>
                        <li><span class="text-sm text-gray-300">México</span></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 pt-8 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-500">&copy; {{ date('Y') }} RepCellPOS. Todos los derechos reservados.</p>
                <div class="flex items-center gap-6 text-sm text-gray-500">
                    <span class="text-xs">Sistema para taller de celulares en México</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>

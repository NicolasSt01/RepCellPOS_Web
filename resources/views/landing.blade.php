<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RepCellPOS — Sistema de Gestión para Talleres de Reparación Celular</title>
    <meta name="description" content="Sistema integral para talleres de reparación de celulares: órdenes de trabajo, inventario, punto de venta, control de caja y seguimiento en tiempo real.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white antialiased">

    <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">RepCell<span class="text-indigo-600">POS</span></span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Funcionalidades</a>
                    <a href="#how-it-works" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Cómo funciona</a>
                    <a href="#pricing" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Precios</a>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">Iniciar sesión</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        Comenzar gratis
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <section class="relative pt-32 pb-20 sm:pt-40 sm:pb-28 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-purple-50"></div>
        <div class="absolute top-20 left-1/4 w-72 h-72 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
        <div class="absolute bottom-20 right-1/4 w-72 h-72 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse" style="animation-delay: 2s"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-1.5 text-sm font-medium text-indigo-700 mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                Nuevo: Portal público de seguimiento para tus clientes
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-tight">
                El sistema que tu taller
                <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">de reparación necesita</span>
            </h1>

            <p class="mt-6 text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                Gestiona órdenes de trabajo, inventario, ventas y caja desde un solo lugar. Tus clientes siguen el estado de su reparación en tiempo real.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-indigo-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-500 hover:shadow-indigo-300 transition-all">
                    Crear mi taller gratis
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
                <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-white px-8 py-3.5 text-base font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50 transition-all">
                    Ver funcionalidades
                </a>
            </div>

            <p class="mt-6 text-sm text-gray-500">Sin tarjeta de crédito. Configuración en 2 minutos.</p>
        </div>
    </section>

    <section id="features" class="py-20 sm:py-28 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-indigo-600 uppercase tracking-wider">Funcionalidades</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900">Todo lo que necesitas en un solo sistema</h2>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Diseñado específicamente para talleres de reparación de equipos celulares.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="group relative bg-white rounded-2xl p-8 shadow-sm ring-1 ring-gray-100 hover:shadow-lg hover:ring-indigo-100 transition-all">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-indigo-600 transition-colors">
                        <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.384 3.065A1 1 0 014.5 17.37V6.63a1 1 0 011.536-.864l5.384 3.065M15.42 15.17l5.384-3.065a1 1 0 000-1.732L15.42 7.31" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Órdenes de Trabajo</h3>
                    <p class="mt-2 text-gray-600 text-sm leading-relaxed">Ciclo completo desde recepción hasta entrega. Timeline en tiempo real, prioridades, estados y anotaciones por técnico.</p>
                </div>

                <div class="group relative bg-white rounded-2xl p-8 shadow-sm ring-1 ring-gray-100 hover:shadow-lg hover:ring-indigo-100 transition-all">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-green-600 transition-colors">
                        <svg class="w-6 h-6 text-green-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Inventario con Kardex</h3>
                    <p class="mt-2 text-gray-600 text-sm leading-relaxed">Control de refacciones y productos con trazabilidad completa. Alertas de stock mínimo y categorías por marca/modelo.</p>
                </div>

                <div class="group relative bg-white rounded-2xl p-8 shadow-sm ring-1 ring-gray-100 hover:shadow-lg hover:ring-indigo-100 transition-all">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-purple-600 transition-colors">
                        <svg class="w-6 h-6 text-purple-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Punto de Venta</h3>
                    <p class="mt-2 text-gray-600 text-sm leading-relaxed">Vende productos y cobra reparaciones con efectivo o tarjeta. Cálculo automático de impuestos y cambio.</p>
                </div>

                <div class="group relative bg-white rounded-2xl p-8 shadow-sm ring-1 ring-gray-100 hover:shadow-lg hover:ring-indigo-100 transition-all">
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-orange-600 transition-colors">
                        <svg class="w-6 h-6 text-orange-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 3h.008v.008H8.25v-.008zm0 3h.008v.008H8.25v-.008zm3-3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm3-3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zM21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Control de Caja</h3>
                    <p class="mt-2 text-gray-600 text-sm leading-relaxed">Apertura y cierre de caja por turno. Reportes desglosados por método de pago y retiros autorizados.</p>
                </div>

                <div class="group relative bg-white rounded-2xl p-8 shadow-sm ring-1 ring-gray-100 hover:shadow-lg hover:ring-indigo-100 transition-all">
                    <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-cyan-600 transition-colors">
                        <svg class="w-6 h-6 text-cyan-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Notificaciones Multicanal</h3>
                    <p class="mt-2 text-gray-600 text-sm leading-relaxed">Informa a tus clientes por WhatsApp, email o llamada. Notificaciones automáticas en cada cambio de estado.</p>
                </div>

                <div class="group relative bg-white rounded-2xl p-8 shadow-sm ring-1 ring-gray-100 hover:shadow-lg hover:ring-indigo-100 transition-all">
                    <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-pink-600 transition-colors">
                        <svg class="w-6 h-6 text-pink-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Portal de Seguimiento</h3>
                    <p class="mt-2 text-gray-600 text-sm leading-relaxed">Tus clientes consultan el estado de su reparación en tiempo real con un link único. Sin login necesario.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="py-20 sm:py-28 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-indigo-600 uppercase tracking-wider">Cómo funciona</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900">De la recepción a la entrega en 4 pasos</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold mb-4">1</div>
                    <h3 class="text-lg font-semibold text-gray-900">Recibe el equipo</h3>
                    <p class="mt-2 text-sm text-gray-600">Registra el dispositivo, el problema y genera el comprobante para el cliente.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold mb-4">2</div>
                    <h3 class="text-lg font-semibold text-gray-900">Diagnostica y cotiza</h3>
                    <p class="mt-2 text-sm text-gray-600">El técnico diagnostica, agrega refacciones del inventario y envía la cotización.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold mb-4">3</div>
                    <h3 class="text-lg font-semibold text-gray-900">Repara</h3>
                    <p class="mt-2 text-sm text-gray-600">El cliente aprueba, el técnico repara y el inventario se descuenta automáticamente.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold mb-4">4</div>
                    <h3 class="text-lg font-semibold text-gray-900">Cobra y entrega</h3>
                    <p class="mt-2 text-sm text-gray-600">Cobra desde el POS, registra en caja y entrega el equipo al cliente.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="py-20 sm:py-28 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-indigo-600 uppercase tracking-wider">Precios</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900">Planes para tu taller</h2>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Todos los planes incluyen 7 días de prueba gratuita sin compromiso.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                @foreach($plans as $plan)
                    <div class="relative bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-8 flex flex-col @if($plan->is_highlight) ring-2 ring-indigo-600 shadow-xl @endif">
                        @if($plan->is_highlight)
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                                <span class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white">Más popular</span>
                            </div>
                        @endif
                        <div class="text-center">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h3>
                            @if($plan->description)
                                <p class="mt-1 text-sm text-gray-500">{{ $plan->description }}</p>
                            @endif
                            <div class="mt-4 flex items-baseline justify-center gap-1">
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
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Límites</p>
                            <ul class="space-y-1 text-xs text-gray-500">
                                @foreach($plan->limits as $limit => $value)
                                    <li>
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

                        <a href="{{ route('register') }}" class="mt-6 block w-full text-center rounded-lg @if($plan->is_highlight) bg-indigo-600 text-white shadow-sm hover:bg-indigo-500 @else bg-gray-50 text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100 @endif px-4 py-3 text-base font-semibold transition-colors">
                            Comenzar prueba gratis
                        </a>
                    </div>
                @endforeach
            </div>

            <p class="mt-10 text-center text-sm text-gray-500">Todos los planes incluyen 7 días de prueba gratuita sin compromiso.</p>
        </div>
    </section>

    <section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-600">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">¿Listo para digitalizar tu taller?</h2>
            <p class="mt-4 text-lg text-indigo-100">Únete a los talleres que ya gestionan sus reparaciones de forma profesional.</p>
            <a href="{{ route('register') }}" class="mt-8 inline-flex items-center rounded-lg bg-white px-8 py-3.5 text-base font-semibold text-indigo-600 shadow-lg hover:bg-indigo-50 transition-all">
                Comenzar ahora — Es gratis
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
    </section>

    <footer class="bg-gray-900 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                    </div>
                    <span class="text-lg font-bold text-white">RepCell<span class="text-indigo-400">POS</span></span>
                </div>
                <p class="text-sm text-gray-400">&copy; {{ date('Y') }} RepCellPOS. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

</body>
</html>

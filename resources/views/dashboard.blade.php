@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Dashboard Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Panel de Control</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Bienvenido, <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ Auth::user()->name }}</span>
                @if(Auth::user()->tenant)
                    — <span class="font-medium text-gray-700 dark:text-gray-300">{{ Auth::user()->tenant->name }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 select-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <span>Hoy es {{ now()->locale('es')->isoFormat('LL') }}</span>
        </div>
    </div>

    <!-- Stats Grid (Flat Colors, subtle hover scale) -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Clients Metric Card -->
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 hover:scale-[1.01] transition-transform duration-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-md text-indigo-600 dark:text-indigo-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Clientes Totales</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $clientsCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Work Orders Card -->
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 hover:scale-[1.01] transition-transform duration-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-amber-50 dark:bg-amber-900/30 rounded-md text-amber-600 dark:text-amber-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.384 3.065A1 1 0 014.5 17.37V6.63a1 1 0 011.536-.864l5.384 3.065M15.42 15.17l5.384-3.065a1 1 0 000-1.732L15.42 7.31" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Órdenes Activas</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activeOrdersCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today Sales Card -->
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 hover:scale-[1.01] transition-transform duration-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-md text-emerald-600 dark:text-emerald-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Ventas Hoy</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($salesToday, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Card -->
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 hover:scale-[1.01] transition-transform duration-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-rose-50 dark:bg-rose-900/30 rounded-md text-rose-600 dark:text-rose-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Productos Activos</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $productsCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OT KPIs grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-slate-50 dark:bg-slate-900/30 rounded-md text-slate-600 dark:text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Pendientes</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pendingOrders }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-orange-50 dark:bg-orange-900/30 rounded-md text-orange-600 dark:text-orange-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.42 15.17l-5.384 3.065A1 1 0 014.5 17.37V6.63a1 1 0 011.536-.864l5.384 3.065M15.42 15.17l5.384-3.065a1 1 0 000-1.732L15.42 7.31"/></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">En Reparación</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $inRepairOrders }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-green-50 dark:bg-green-900/30 rounded-md text-green-600 dark:text-green-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Completadas (este mes)</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $completedThisMonth }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-red-50 dark:bg-red-900/30 rounded-md text-red-600 dark:text-red-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Sin Técnico Asignado</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $unassignedOrders }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    @if($lowStockProducts->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-900/50 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 p-2 bg-red-50 dark:bg-red-900/30 rounded-md text-red-600 dark:text-red-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Productos con Stock Bajo</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Producto</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Stock Actual</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Stock Mínimo</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($lowStockProducts as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">{{ $product->stock }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->min_stock }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Reabastecer</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Order Status Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                </svg>
                Distribución de Órdenes de Trabajo
            </h3>
            
            @php
                $totalOrdersChart = collect($orderStatusChartData)->sum('count');
            @endphp

            @if($totalOrdersChart > 0)
                <div class="space-y-4">
                    @foreach($orderStatusChartData as $statusData)
                        @php
                            $pct = ($statusData['count'] / $totalOrdersChart) * 100;
                            // Asignar color plano específico por estado
                            $barColor = 'bg-gray-500';
                            switch($statusData['status']) {
                                case 'recibida': $barColor = 'bg-blue-500'; break;
                                case 'en_espera': $barColor = 'bg-slate-400'; break;
                                case 'en_revision': $barColor = 'bg-amber-500'; break;
                                case 'diagnosticada': $barColor = 'bg-purple-500'; break;
                                case 'cotizacion_enviada': $barColor = 'bg-teal-500'; break;
                                case 'cotizacion_aprobada': $barColor = 'bg-indigo-500'; break;
                                case 'en_reparacion': $barColor = 'bg-orange-500'; break;
                                case 'reparada': $barColor = 'bg-emerald-500'; break;
                                case 'terminada': $barColor = 'bg-green-600'; break;
                                case 'cancelada': $barColor = 'bg-rose-500'; break;
                            }
                        @endphp
                        <div class="group">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $statusData['label'] }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $statusData['count'] }}</span>
                                    <span class="text-xs text-gray-400 select-none">({{ number_format($pct, 0) }}%)</span>
                                </div>
                            </div>
                            <!-- Bar track -->
                            <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                <!-- Progress bar with flat color and transition -->
                                <div class="{{ $barColor }} h-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500 text-sm">
                    <svg class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25-1.875V16.5m-3-3h6m-9 1.5H3c-.621 0-1.125-.504-1.125-1.125V3.75c0-.621.504-1.125 1.125-1.125h9.75c.621 0 1.125.504 1.125 1.125v3.5m-9.75 0h4.875c.621 0 1.125.504 1.125 1.125V12" />
                    </svg>
                    <span>Sin datos de órdenes registrados</span>
                </div>
            @endif
        </div>

        <!-- Chart 2: Weekly Sales Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" />
                </svg>
                Ventas de los Últimos 7 Días
            </h3>

            @php
                $maxSalesAmount = collect($weeklySalesData)->max('amount');
                $maxSalesAmount = $maxSalesAmount > 0 ? $maxSalesAmount : 100;
            @endphp

            <!-- Columns Container -->
            <div class="h-64 flex flex-col justify-end pt-4">
                <div class="flex items-end justify-between h-full px-2 border-b border-gray-200 dark:border-gray-700 pb-1">
                    @foreach($weeklySalesData as $daySales)
                        @php
                            $colHeightPct = ($daySales['amount'] / $maxSalesAmount) * 85;
                            // Altura mínima para que sea visible incluso en 0 ventas
                            $colHeightPct = max(3, $colHeightPct);
                        @endphp
                        <div class="flex flex-col items-center flex-1 group relative">
                            <!-- Tooltip on Hover -->
                            <div class="absolute -top-10 scale-0 group-hover:scale-100 transition-all duration-150 bg-gray-900 dark:bg-gray-700 text-white text-xs px-2.5 py-1.5 rounded shadow-md font-bold select-none pointer-events-none z-10 whitespace-nowrap">
                                ${{ number_format($daySales['amount'], 2) }}
                            </div>

                            <!-- Value display above column -->
                            <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 select-none">
                                ${{ number_format($daySales['amount'], 0) }}
                            </span>

                            <!-- Vertical flat column -->
                            <div class="w-8 sm:w-10 bg-indigo-500 dark:bg-indigo-600 hover:bg-indigo-600 dark:hover:bg-indigo-500 rounded-t-sm transition-all duration-300" 
                                 style="height: {{ $colHeightPct }}%">
                            </div>

                            <!-- Day Name Label -->
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-2 select-none">
                                {{ $daySales['day'] }}
                            </span>
                            <!-- Date Label -->
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 select-none">
                                {{ $daySales['date'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Technician Workload Section -->
    @if($technicianWorkload->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Carga de Trabajo por Técnico
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Técnico</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Órdenes Activas</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Barra</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php $maxLoad = $technicianWorkload->max('total') ?: 1; @endphp
                    @foreach($technicianWorkload as $tw)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $tw->assignedTechnician->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                            {{ $tw->total }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-purple-500 h-full rounded-full" style="width: {{ ($tw->total / $maxLoad) * 100 }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Work Orders Table Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                Órdenes de Trabajo Recientes
            </h3>
            @can('work_orders.create')
                <a href="{{ route('work_orders.create') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a.75.75 0 01.75.75v4.3l2.6-1.5a.75.75 0 11.75 1.3l-3.35 1.93a.75.75 0 01-.75 0l-3.35-1.93a.75.75 0 11.75-1.3l2.6 1.5V5.75A.75.75 0 0110 5z" clip-rule="evenodd" />
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.586L7.06 7.646a.75.75 0 10-1.06 1.06l3.5 3.5a.75.75 0 001.06 0l3.5-3.5a.75.75 0 10-1.06-1.06L10.75 9.336V4.75z" />
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v4.586l2.293-2.293a1 1 0 011.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 8.586V4a1 1 0 011-1z" clip-rule="evenodd" />
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L6.707 8.293a1 1 0 00-1.414 1.414l3.5 3.5a1 1 0 001.414 0l3.5-3.5a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                        <!-- Fallback standard plus icon -->
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Nueva Orden
                </a>
            @endcan
        </div>

        @if($recentOrders->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50">
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Folio</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dispositivo</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Técnico</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prioridad</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recibida</th>
                            <th scope="col" class="px-4 py-3 class=text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recentOrders as $order)
                            @php
                                // Configurar clases del badge por estado (colores planos)
                                $statusBadgeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
                                switch($order->status) {
                                    case 'recibida':
                                        $statusBadgeClass = 'bg-blue-100 text-blue-800 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/40';
                                        break;
                                    case 'en_espera':
                                        $statusBadgeClass = 'bg-slate-100 text-slate-800 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
                                        break;
                                    case 'en_revision':
                                        $statusBadgeClass = 'bg-amber-100 text-amber-800 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/40';
                                        break;
                                    case 'diagnosticada':
                                        $statusBadgeClass = 'bg-purple-100 text-purple-800 border border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800/40';
                                        break;
                                    case 'cotizacion_enviada':
                                        $statusBadgeClass = 'bg-teal-100 text-teal-800 border border-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:border-teal-800/40';
                                        break;
                                    case 'cotizacion_aprobada':
                                        $statusBadgeClass = 'bg-indigo-100 text-indigo-800 border border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800/40';
                                        break;
                                    case 'en_reparacion':
                                        $statusBadgeClass = 'bg-orange-100 text-orange-800 border border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800/40';
                                        break;
                                    case 'reparada':
                                        $statusBadgeClass = 'bg-emerald-100 text-emerald-800 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/40';
                                        break;
                                    case 'terminada':
                                        $statusBadgeClass = 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800/40';
                                        break;
                                    case 'cancelada':
                                        $statusBadgeClass = 'bg-rose-100 text-rose-800 border border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800/40';
                                        break;
                                }

                                // Configurar prioridad
                                $priorityLabel = 'Baja';
                                $priorityColor = 'bg-emerald-500';
                                switch($order->priority) {
                                    case 'media':
                                        $priorityLabel = 'Media';
                                        $priorityColor = 'bg-amber-500';
                                        break;
                                    case 'alta':
                                        $priorityLabel = 'Alta';
                                        $priorityColor = 'bg-rose-500';
                                        break;
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                    #{{ $order->work_order_number }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $order->client ? $order->client->name : 'N/A' }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $order->device_brand }} {{ $order->device_model }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold {{ $statusBadgeClass }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $order->assignedTechnician->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-2.5 w-2.5 rounded-full {{ $priorityColor }} inline-block"></span>
                                        <span class="font-medium text-xs">{{ $priorityLabel }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('work_orders.show', $order) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-bold transition-colors">
                                        Ver detalles
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500 text-sm">
                <svg class="h-16 w-16 text-gray-300 dark:text-gray-600 mb-2 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="font-medium">No se encontraron órdenes de trabajo registradas recientemente.</p>
                <p class="text-xs text-gray-400 mt-1">Las órdenes creadas por los secretarios aparecerán listadas aquí.</p>
            </div>
        @endif
    </div>
</div>
@endsection

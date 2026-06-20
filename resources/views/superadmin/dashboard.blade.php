@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Panel de Control</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Métricas globales del ecosistema RepCellPOS</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.tenants.index') }}"
               class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                Ver Tenants
            </a>
            <a href="{{ route('admin.finances') }}"
               class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Finanzas
            </a>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">MRR</dt>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $mrrChange >= 0 ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' }}">
                        {{ $mrrChange >= 0 ? '+' : '' }}{{ $mrrChange }}%
                    </span>
                </div>
                <dd class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($mrr, 2) }}</dd>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Ingreso recurrente mensual</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Ingresos del Mes</dt>
                <dd class="mt-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400">${{ number_format($monthlyRevenue, 2) }}</dd>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $activeSubscriptions }} suscripciones activas</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Tenants</dt>
                <dd class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $totalTenants }}</dd>
                <div class="mt-1 flex items-center gap-3 text-xs">
                    <span class="text-green-600 dark:text-green-400">{{ $activeTenants }} activos</span>
                    <span class="text-blue-600 dark:text-blue-400">{{ $trialTenants }} en prueba</span>
                    @if($expiredTrials > 0)
                    <span class="text-red-600 dark:text-red-400">{{ $expiredTrials }} vencidos</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Usuarios</dt>
                <dd class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUsers) }}</dd>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">En toda la plataforma</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Plan Distribution -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Distribución de Planes</h2>
            </div>
            <div class="p-6">
                @if($planDistribution->count() > 0)
                    @php $maxPlanCount = $planDistribution->max('tenants_count') ?: 1; @endphp
                    <div class="space-y-4">
                        @foreach($planDistribution as $plan)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $plan->name }}</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($plan->price, 2) }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $loop->first ? 'bg-indigo-500' : ($loop->iteration === 2 ? 'bg-blue-500' : 'bg-amber-500') }}" style="width: {{ ($plan->tenants_count / $maxPlanCount) * 100 }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 min-w-[2rem] text-right">{{ $plan->tenants_count }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No hay planes configurados.</p>
                @endif
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Próximos a Vencer</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Próximos 7 días</p>
            </div>
            <div class="p-6">
                @if($expiringSoon->count() > 0)
                    <div class="space-y-3">
                        @foreach($expiringSoon as $sub)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $sub->tenant?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $sub->tenant?->plan?->name ?? 'Sin plan' }} · ${{ number_format($sub->amount, 2) }}</p>
                            </div>
                            <span class="text-xs font-medium text-amber-600 dark:text-amber-400 whitespace-nowrap">{{ $sub->end_date?->diffForHumans() ?? 'N/A' }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Ninguna suscripción por vencer.</p>
                @endif
            </div>
        </div>

        <!-- Revenue Mini Chart -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ingresos Mensuales</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Últimos 6 meses</p>
            </div>
            <div class="p-6">
                @if($revenueHistory->count() > 0)
                    @php $maxRevenue = max($revenueHistory->toArray()) ?: 1; @endphp
                    <div class="flex items-end justify-between gap-2 h-32">
                        @foreach($revenueHistory as $month => $total)
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">${{ number_format($total / 1000, 1) }}k</span>
                            <div class="w-full bg-indigo-100 dark:bg-indigo-900/30 rounded-t-md" style="height: {{ ($total / $maxRevenue) * 100 }}%; min-height: 4px;">
                                <div class="w-full h-full bg-indigo-500 dark:bg-indigo-400 rounded-t-md opacity-80 hover:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ substr($month, 5, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Sin datos de ingresos aún.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Pending Payments -->
    @if($pendingPayments->count() > 0)
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-amber-200 dark:border-amber-700 rounded-2xl overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/10">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-300">Pagos Pendientes ({{ $pendingPayments->count() }})</h2>
                <a href="{{ route('admin.finances') }}" class="text-sm font-medium text-amber-700 dark:text-amber-400 hover:text-amber-600">Ver todos</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @foreach($pendingPayments as $payment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $payment->tenant?->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                ${{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $payment->paid_via ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.finances.confirm', $payment->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-green-500">
                                            Confirmar
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.finances.reject', $payment->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-500">
                                            Rechazar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Tenants -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tenants Registrados Recientemente</h2>
            <a href="{{ route('admin.tenants.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Ver todos</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Suscripción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($recentTenants as $tenant)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tenant->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tenant->slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tenant->is_active)
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">Activo</span>
                                @else
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($tenant->subscription_status === 'trial')
                                    <span class="text-blue-600 dark:text-blue-400">Trial</span>
                                @elseif($tenant->subscription_status === 'active')
                                    <span class="text-green-600 dark:text-green-400">Activa</span>
                                @else
                                    <span class="text-gray-400">{{ $tenant->subscription_status ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $tenant->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No hay tenants registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
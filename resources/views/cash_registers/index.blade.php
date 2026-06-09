@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Control de Caja</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestión de aperturas y cierres de caja</p>
        </div>
        @if(!$openRegister)
        <button onclick="document.getElementById('open-modal').classList.remove('hidden')"
            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            Abrir caja
        </button>
        @endif
    </div>

    @if($openRegister)
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Caja Abierta</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Abierta por</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $openRegister->user->name }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha apertura</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $openRegister->opened_at->format('d/m/Y H:i') }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fondo inicial</dt><dd class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100">${{ number_format($openRegister->opening_amount, 2) }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Efectivo esperado</dt><dd class="mt-1 text-sm font-bold text-indigo-600 dark:text-indigo-400">${{ number_format($openRegister->getExpectedCash(), 2) }}</dd></div>
            </dl>
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <p class="text-sm text-green-700 dark:text-green-400">Ventas efectivo</p>
                    <p class="text-xl font-bold text-green-800 dark:text-green-300">${{ number_format($openRegister->getTotalCashSales(), 2) }}</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <p class="text-sm text-blue-700 dark:text-blue-400">Ventas tarjeta/transfer.</p>
                    <p class="text-xl font-bold text-blue-800 dark:text-blue-300">${{ number_format($openRegister->getTotalCardSales(), 2) }}</p>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                    <p class="text-sm text-red-700 dark:text-red-400">Retiros</p>
                    <p class="text-xl font-bold text-red-800 dark:text-red-300">${{ number_format($openRegister->getTotalWithdrawals(), 2) }}</p>
                </div>
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
                    <p class="text-sm text-orange-700 dark:text-orange-400">Devoluciones</p>
                    <p class="text-xl font-bold text-orange-800 dark:text-orange-300">${{ number_format($openRegister->getTotalReturns(), 2) }}</p>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button onclick="document.getElementById('close-modal').classList.remove('hidden')"
                    class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors">
                    Cerrar caja
                </button>
                <button onclick="document.getElementById('withdraw-modal').classList.remove('hidden')"
                    class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 transition-colors">
                    Retiro parcial
                </button>
            </div>

            @if($movements->isNotEmpty())
            <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Movimientos de esta caja</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Motivo</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Monto</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Autorizado por</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($movements as $movement)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-2 text-sm">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium
                                        {{ $movement->type === 'retiro' ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                        {{ $movement->type === 'devolucion' ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400' : '' }}">
                                        {{ $movement->type === 'devolucion' ? 'Devolución' : ucfirst($movement->type) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $movement->reason }}</td>
                                <td class="px-3 py-2 text-sm text-right font-medium text-red-600 dark:text-red-400">-${{ number_format($movement->amount, 2) }}</td>
                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $movement->authorized_by ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Historial de Cajas</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Usuario</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Apertura</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fondo</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cierre</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($registers as $register)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $register->user->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $register->opened_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">${{ number_format($register->opening_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">{{ $register->closing_amount ? '$' . number_format($register->closing_amount, 2) : '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                    {{ $register->status === 'abierta' ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-gray-50 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400' }}">
                                    {{ ucfirst($register->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay registros de caja.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($registers->hasPages())
            <div class="mt-4">{{ $registers->links() }}</div>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Historial de Retiros</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Caja</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Motivo</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Monto</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Autorizado por</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($allMovements as $movement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $movement->cashRegister->user->name }} — #{{ $movement->cash_register_id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $movement->reason }}</td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-red-600 dark:text-red-400">-${{ number_format($movement->amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $movement->authorized_by ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay retiros registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($allMovements->hasPages())
            <div class="mt-4">{{ $allMovements->links() }}</div>
            @endif
        </div>
    </div>
</div>

<div id="open-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Abrir Caja</h3>
            <form method="POST" action="{{ route('cash_registers.open') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Fondo inicial (efectivo) <span class="text-red-500">*</span></label>
                    <input type="number" name="opening_amount" step="0.01" min="0" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="this.closest('[role=dialog]').classList.add('hidden')" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Abrir caja</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($openRegister)
<div id="close-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Cerrar Caja</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Efectivo esperado: <span class="font-bold text-indigo-600">${{ number_format($openRegister->getExpectedCash(), 2) }}</span></p>
            <form method="POST" action="{{ route('cash_registers.close', $openRegister) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Efectivo contado <span class="text-red-500">*</span></label>
                    <input type="number" name="closing_amount" step="0.01" min="0" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Notas</label>
                    <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="this.closest('[role=dialog]').classList.add('hidden')" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                    <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors">Cerrar caja</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="withdraw-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Retiro Parcial</h3>
            <form method="POST" action="{{ route('cash_registers.withdraw', $openRegister) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Monto <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0.01" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Motivo <span class="text-red-500">*</span></label>
                    <input type="text" name="reason" required
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">Autorizado por</label>
                    <input type="text" name="authorized_by"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="this.closest('[role=dialog]').classList.add('hidden')" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                    <button type="submit" class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 transition-colors">Registrar retiro</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

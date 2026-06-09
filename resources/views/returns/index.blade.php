@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Devoluciones</h1>
        <a href="{{ route('returns.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            + Nueva Devolución
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Venta</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Atendió</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Monto Devuelto</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Artículos</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($returns as $return)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">#{{ $return->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                            <a href="{{ route('sales.show', $return->sale) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                #{{ $return->sale_id }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $return->user->name }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-red-600 dark:text-red-400">${{ number_format($return->refund_total, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">{{ $return->returnItems->sum('quantity') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ $return->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No hay devoluciones registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

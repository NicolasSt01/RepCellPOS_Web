@props([
    'route' => null,
    'dateFrom' => request('date_from'),
    'dateTo' => request('date_to'),
    'showDateRange' => true,
    'extraFilters' => '',
])

<form method="GET" action="{{ $route }}" class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-4">
    <div class="flex flex-wrap items-end gap-3">
        @if($showDateRange)
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Desde</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}"
                class="block rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Hasta</label>
            <input type="date" name="date_to" value="{{ $dateTo }}"
                class="block rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 text-sm">
        </div>
        <div class="flex gap-1">
            @foreach(['hoy' => 'Hoy', '7d' => '7 días', '30d' => '30 días', 'mes' => 'Mes', 'trimestre' => 'Trimestre', 'año' => 'Año'] as $key => $label)
            @php
                $preset = match($key) {
                    'hoy' => [now()->format('Y-m-d'), now()->format('Y-m-d')],
                    '7d' => [now()->subDays(7)->format('Y-m-d'), now()->format('Y-m-d')],
                    '30d' => [now()->subDays(30)->format('Y-m-d'), now()->format('Y-m-d')],
                    'mes' => [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
                    'trimestre' => [now()->startOfQuarter()->format('Y-m-d'), now()->format('Y-m-d')],
                    'año' => [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
                };
            @endphp
            <a href="{{ request()->fullUrlWithQuery(['date_from' => $preset[0], 'date_to' => $preset[1]]) }}"
                class="px-2.5 py-1.5 text-xs font-medium rounded-md transition-colors {{ $dateFrom === $preset[0] && $dateTo === $preset[1] ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
        @endif

        {{ $extraFilters }}

        <div class="flex gap-2">
            <button type="submit"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                Filtrar
            </button>
            @if(request()->anyFilled(['date_from', 'date_to', 'status', 'priority', 'assigned_to', 'technician', 'category', 'payment_method', 'type', 'product']))
            <a href="{{ route(explode('?', request()->fullUrl())[0] == request()->url() ? request()->route()->getName() : '#') }}"
                class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                Limpiar
            </a>
            @endif
        </div>
    </div>
</form>

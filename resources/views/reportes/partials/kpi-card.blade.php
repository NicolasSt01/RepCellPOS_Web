@props([
    'label' => '',
    'value' => '',
    'subtext' => null,
    'color' => 'indigo',
    'icon' => null,
])

@php
    $colorClasses = [
        'indigo' => 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300',
        'green' => 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300',
        'red' => 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300',
        'yellow' => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300',
        'blue' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300',
        'gray' => 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300',
    ][$color] ?? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300';
@endphp

<div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 {{ $colorClasses }}">
    <p class="text-xs font-medium opacity-75">{{ $label }}</p>
    <p class="mt-1 text-2xl font-bold">{{ $value }}</p>
    @if($subtext)
    <p class="mt-0.5 text-xs opacity-60">{{ $subtext }}</p>
    @endif
</div>

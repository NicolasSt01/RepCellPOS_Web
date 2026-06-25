@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto mt-16">
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl p-8 text-center">
        <div class="mx-auto w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-3">Sesión suspendida</h1>

        <p class="text-gray-500 dark:text-gray-400 mb-6">
            Por el momento tu sesión ha sido desactivada por falta de pago.
            Contacta a tu administrador para regularizar la situación.
        </p>

        @if($admin)
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-5 mb-6 text-left">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3">Administrador del taller</h3>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold text-lg">{{ substr($admin->name, 0, 1) }}</span>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $admin->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $admin->email }}</p>
                </div>
            </div>
        </div>
        @endif

        <p class="text-xs text-gray-400 dark:text-gray-500">
            Si eres el administrador, <a href="{{ route('subscription.upgrade') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">haz clic aquí</a> para gestionar tu plan.
        </p>

        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Cerrar sesión
            </button>
        </form>
    </div>
</div>
@endsection

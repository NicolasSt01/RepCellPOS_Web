<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100 dark:bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'RepCellPOS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full">
    <div class="min-h-full" x-data="{ sidebarOpen: true, mobileSidebarOpen: false }">
        <!-- Mobile Sidebar (Drawer) -->
        <div x-show="mobileSidebarOpen" class="relative z-50 lg:hidden" x-cloak>
            <!-- Mobile Sidebar Backdrop -->
            <div x-show="mobileSidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/80" 
                 @click="mobileSidebarOpen = false"></div>

            <div class="fixed inset-0 flex">
                <!-- Mobile Sidebar Content Drawer -->
                <div x-show="mobileSidebarOpen"
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative mr-16 flex w-full max-w-xs flex-1">
                    
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-gray-800 px-6 pb-4 shadow-xl">
                        @include('layouts.partials.sidebar-content')
                        
                        <!-- Mobile Close Button -->
                        <div class="mt-auto pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="button" 
                                    @click="mobileSidebarOpen = false" 
                                    class="w-full flex items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors duration-150">
                                <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Cerrar menú</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col transition-all duration-300 ease-in-out"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
             x-cloak>
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 px-6 pb-4">
                @include('layouts.partials.sidebar-content')
                
                <!-- Desktop Bottom Collapse Button -->
                <div class="mt-auto pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" 
                            @click="sidebarOpen = false" 
                            class="w-full flex items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors duration-150">
                        <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                        </svg>
                        <span>Contraer menú</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Area Wrapper -->
        <div class="transition-all duration-300 ease-in-out"
             :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-0'">
            
            <!-- Navbar -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <!-- Mobile Hamburger Button -->
                <button type="button" 
                        class="-m-2.5 p-2.5 text-gray-700 dark:text-gray-300 lg:hidden hover:text-gray-955 dark:hover:text-white transition-colors" 
                        @click="mobileSidebarOpen = true">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Desktop Hamburger Button (Only visible when sidebar is closed/collapsed) -->
                <button type="button" 
                        x-show="!sidebarOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="hidden lg:flex -m-2.5 p-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-955 dark:hover:text-white transition-colors" 
                        @click="sidebarOpen = true"
                        x-cloak>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Search/Brand Left aligned section -->
                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1 items-center">
                        @if(Auth::user()->tenant)
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-md select-none border border-gray-200 dark:border-gray-700">
                                🏢 {{ Auth::user()->tenant->name }}
                            </span>
                        @endif
                    </div>
                    
                    <!-- Right navbar: Collapsible User settings and Logout -->
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- User Dropdown Menu -->
                        <div class="relative" x-data="{ userDropdownOpen: false }" @click.away="userDropdownOpen = false">
                            <button type="button" 
                                    @click="userDropdownOpen = !userDropdownOpen" 
                                    class="-m-1.5 flex items-center p-1.5 focus:outline-none" 
                                    id="user-menu-button" 
                                    aria-expanded="false" 
                                    aria-haspopup="true">
                                <span class="sr-only">Abrir menú de usuario</span>
                                <!-- Profile Initials Badge (Flat design, no gradient) -->
                                <div class="h-9 w-9 rounded-md bg-indigo-600 flex items-center justify-center text-sm font-bold text-white uppercase select-none transition-colors duration-150 hover:bg-indigo-700">
                                    {{ substr(Auth::user()->name, 0, 2) }}
                                </div>
                                <span class="hidden lg:flex lg:items-center">
                                    <span class="ml-3 text-sm font-semibold leading-6 text-gray-700 dark:text-gray-300 hover:text-gray-955 dark:hover:text-white transition-colors" aria-hidden="true">
                                        {{ Auth::user()->name }}
                                    </span>
                                    <svg class="ml-2 h-5 w-5 text-gray-400 transition-transform duration-200" :class="userDropdownOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>

                            <!-- Dropdown Panel (Flat design, animations discretas) -->
                            <div x-show="userDropdownOpen"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 z-50 mt-2.5 w-64 origin-top-right rounded-md bg-white dark:bg-gray-800 py-2 shadow-lg border border-gray-200 dark:border-gray-700 focus:outline-none"
                                 role="menu" 
                                 aria-orientation="vertical" 
                                 aria-labelledby="user-menu-button" 
                                 tabindex="-1"
                                 x-cloak>
                                
                                <!-- User Info Header -->
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Usuario</p>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
                                    @if(Auth::user()->roles->isNotEmpty())
                                        <span class="mt-1.5 inline-flex items-center rounded-md bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-400 ring-1 ring-inset ring-indigo-700/10 dark:ring-indigo-400/20">
                                            {{ Auth::user()->roles->first()->name }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Configuration Section -->
                                <div class="py-1">
                                    <div class="px-4 py-1 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Configuración</div>
                                    @can('settings.company')
                                        <a href="{{ route('settings.company') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" role="menuitem">
                                            Mi Empresa
                                        </a>
                                    @endcan
                                    @can('settings.users')
                                        <a href="{{ route('settings.users') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" role="menuitem">
                                            Usuarios
                                        </a>
                                    @endcan
                                    @can('settings.roles')
                                        <a href="{{ route('settings.roles') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" role="menuitem">
                                            Roles y Permisos
                                        </a>
                                    @endcan
                                    @can('settings.clauses')
                                        <a href="{{ route('settings.clauses') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" role="menuitem">
                                            Cláusulas
                                        </a>
                                    @endcan
                                    @can('settings.taxes')
                                        <a href="{{ route('settings.taxes') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" role="menuitem">
                                            Impuestos y Formato
                                        </a>
                                    @endcan
                                </div>

                                <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                                <!-- Logout Section -->
                                <div class="py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" role="menuitem">
                                            Cerrar sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Main Content -->
            <main class="py-6 px-4 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800">
                        <p class="text-sm text-green-800 dark:text-green-200 font-medium">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIDAN - Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Figtree', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #10b981; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #34d399; }
    </style>
</head>
<body class="bg-[#0a0f1e] text-white antialiased" x-data="{ sidebarOpen: false, profileOpen: false }" x-cloak>

<div class="min-h-screen flex">
    
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0f172a] border-r border-white/5 lg:translate-x-0 transition-transform duration-300 flex flex-col"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <div class="flex items-center gap-3 h-16 px-6 border-b border-white/5">
            <div class="w-9 h-9 rounded-lg bg-emerald-500 flex items-center justify-center font-bold text-white text-lg">S</div>
            <span class="text-xl font-bold tracking-tight">SIDAN</span>
        </div>

        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
            @php 
                $rol = auth()->user()->rol->nombre ?? 'usuario';
                $is_admin = in_array($rol, ['admin', 'superadmin']);
                $dashboardRoute = $is_admin ? route('admin.dashboard') : route('user.dashboard');
            @endphp
            
            <a href="{{ $dashboardRoute }}" 
               class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('*.dashboard') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>

            @if($is_admin)
                <div class="pt-6 mt-6 border-t border-white/5">
                    <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Administración</p>
                    
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Actividades
                    </a>
                    
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Pagos
                    </a>
                    
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Reportes
                    </a>
                    
                    <a href="{{ route('admin.users.index') }}" 
                    class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-gray-400 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.users.*') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Usuarios
                    </a>
                </div>
            @endif
        </nav>

        <div class="p-4 border-t border-white/5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center font-bold text-white shadow-lg shadow-emerald-500/30">
                    {{ strtoupper(substr(auth()->user()->nombres, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->nombres }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ ucfirst($rol) }}</p>
                </div>
            </div>
        </div>
    </aside>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

    <div class="flex-1 flex flex-col lg:pl-64 min-w-0">
        
        <header class="sticky top-0 z-30 h-16 bg-[#0f172a]/80 backdrop-blur-xl border-b border-white/5 flex items-center justify-between px-4 lg:px-8">
            
            <div class="flex items-center gap-4 flex-1">
                <button @click="sidebarOpen = true" class="text-gray-400 hover:text-white lg:hidden transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                
                <div class="hidden md:flex items-center gap-3 flex-1 max-w-xl mx-8">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" placeholder="Buscar actividades, estudiantes, pagos..." 
                               class="w-full bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button class="relative p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                </button>

                <button class="hidden sm:block p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition group">
                        <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="hidden sm:inline font-medium">Cerrar sesión</span>
                    </button>
                </form>

                <div class="relative ml-2">
                    <button @click="profileOpen = !profileOpen" @click.outside="profileOpen = false" 
                            class="flex items-center gap-2 p-1 pr-3 rounded-lg hover:bg-white/5 transition">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center font-bold text-white text-sm">
                            {{ strtoupper(substr(auth()->user()->nombres, 0, 1)) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-white">{{ auth()->user()->apellidos }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="profileOpen" x-transition 
                         class="absolute right-0 mt-2 w-56 bg-[#0f172a] border border-white/10 rounded-xl shadow-2xl py-2 z-50"
                         style="display: none;">
                        <div class="px-4 py-3 border-b border-white/5">
                            <p class="text-sm font-semibold text-white">{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->correo }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Mi perfil
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                            Configuración
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Ayuda
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-8 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
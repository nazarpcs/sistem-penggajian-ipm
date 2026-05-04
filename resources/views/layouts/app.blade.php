<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Penggajian') — PT IPM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm lg:hidden"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:leave="transition-opacity ease-in duration-150"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-800 transform transition-transform duration-200 ease-in-out lg:translate-x-0 flex flex-col">
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-700">
            <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center">
                <span class="text-white font-bold text-sm">IPM</span>
            </div>
            <div>
                <p class="text-white font-semibold text-sm leading-tight">PT Indah Permata</p>
                <p class="text-slate-400 text-xs">Sistem Penggajian</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            @role('admin')
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('admin.dashboard') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.karyawan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('admin.karyawan.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Karyawan
            </a>
            <a href="{{ route('admin.pt-klien.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('admin.pt-klien.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                PT Klien
            </a>
            <a href="{{ route('admin.absensi.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('admin.absensi.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Absensi
            </a>
            <a href="{{ route('admin.penggajian.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('admin.penggajian.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Penggajian
            </a>
            <a href="{{ route('admin.invoice.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('admin.invoice.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Invoice
            </a>

            <div class="pt-4 mt-4 border-t border-slate-700">
                <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Laporan</p>
            </div>
            <a href="{{ route('admin.laporan.absensi') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('admin.laporan.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan
            </a>
            <a href="{{ route('admin.audit-log.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('admin.audit-log.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Audit Log
            </a>
            @endrole

            @role('pemilik_pt')
            <a href="{{ route('owner.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('owner.dashboard') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('owner.invoice.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('owner.invoice.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Invoice Approval
            </a>
            <a href="{{ route('owner.laporan.absensi') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('owner.laporan.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan
            </a>
            @endrole

            @role('karyawan')
            <a href="{{ route('karyawan.profil.show') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('karyawan.profil.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profil Saya
            </a>
            <a href="{{ route('karyawan.absensi.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('karyawan.absensi.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Absensi Saya
            </a>
            <a href="{{ route('karyawan.slip-gaji.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('karyawan.slip-gaji.*') ? 'bg-slate-700 text-white border-l-4 border-indigo-500 pl-2' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Slip Gaji
            </a>
            @endrole
        </nav>

        {{-- User info at bottom --}}
        <div class="px-4 py-4 border-t border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-xs text-slate-400 capitalize">{{ str_replace('_', ' ', auth()->user()->role ?? '') }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="lg:ml-64 min-h-screen flex flex-col">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 bg-white border-b border-slate-200">
            <div class="flex items-center justify-between px-4 lg:px-8 py-4">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600" aria-label="Toggle sidebar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-xl font-semibold text-slate-900">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3" x-data="{ profileOpen: false }">
                    <div class="relative">
                        <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-100 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="hidden sm:block text-sm text-slate-700">{{ auth()->user()->name ?? 'User' }}</span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="profileOpen" @click.away="profileOpen = false" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-50">
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <button type="submit" formaction="{{ url('/logout') }}" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="px-4 lg:px-8 pt-4">
            @include('components.flash-message')
        </div>

        {{-- Page content --}}
        <main class="flex-1 px-4 lg:px-8 py-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>

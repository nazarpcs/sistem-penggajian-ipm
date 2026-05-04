@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="w-full max-w-md" x-data="{ loading: false }">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-xl bg-indigo-600 flex items-center justify-center mx-auto mb-4">
                <span class="text-white font-bold text-xl">IPM</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Selamat Datang</h1>
            <p class="text-sm text-slate-500 mt-1">Masuk ke Sistem Penggajian PT IPM</p>
        </div>

        {{-- Error messages --}}
        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-lg mb-6 text-sm" role="alert">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        @if(session('status'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-3 rounded-lg mb-6 text-sm" role="alert">
            <p>{{ session('status') }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" @submit="loading = true">
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors"
                           placeholder="nama@email.com">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors"
                           placeholder="Masukkan password">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-600">Ingat saya</span>
                    </label>
                    <a href="{{ url('/password/forgot') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Lupa password?</a>
                </div>
                <button type="submit" :disabled="loading"
                        class="w-full bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="loading ? 'Memproses...' : 'Masuk'">Masuk</span>
                </button>
            </div>
        </form>
    </div>
    <p class="text-center text-xs text-slate-400 mt-6">&copy; {{ date('Y') }} PT Indah Permata Mandiri. All rights reserved.</p>
</div>
@endsection

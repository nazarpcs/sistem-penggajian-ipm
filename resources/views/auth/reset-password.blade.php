@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
<div class="w-full max-w-md" x-data="{ loading: false }">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-xl bg-indigo-600 flex items-center justify-center mx-auto mb-4">
                <span class="text-white font-bold text-xl">IPM</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Reset Password</h1>
            <p class="text-sm text-slate-500 mt-1">Buat password baru untuk akun Anda</p>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-lg mb-6 text-sm" role="alert">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ url('/password/reset') }}" @submit="loading = true">
            @csrf
            <input type="hidden" name="token" value="{{ $token ?? '' }}">
            <div class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email ?? '') }}" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                    <input type="password" id="password" name="password" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors"
                           placeholder="Minimal 8 karakter">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors"
                           placeholder="Ulangi password baru">
                </div>
                <button type="submit" :disabled="loading"
                        class="w-full bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="loading ? 'Menyimpan...' : 'Simpan Password Baru'">Simpan Password Baru</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

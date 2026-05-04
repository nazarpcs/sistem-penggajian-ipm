@extends('layouts.guest')

@section('title', 'Lupa Password')

@section('content')
<div class="w-full max-w-md" x-data="{ loading: false }">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-xl bg-indigo-600 flex items-center justify-center mx-auto mb-4">
                <span class="text-white font-bold text-xl">IPM</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Lupa Password</h1>
            <p class="text-sm text-slate-500 mt-1">Masukkan email Anda untuk menerima tautan reset password</p>
        </div>

        @if(session('status'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-3 rounded-lg mb-6 text-sm" role="alert">
            <p>{{ session('status') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-lg mb-6 text-sm" role="alert">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ url('/password/forgot') }}" @submit="loading = true">
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors"
                           placeholder="nama@email.com">
                </div>
                <button type="submit" :disabled="loading"
                        class="w-full bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="loading ? 'Mengirim...' : 'Kirim Tautan Reset'">Kirim Tautan Reset</span>
                </button>
                <a href="{{ route('login') }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-700 font-medium">Kembali ke Login</a>
            </div>
        </form>
    </div>
</div>
@endsection

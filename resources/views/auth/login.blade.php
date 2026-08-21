@extends('layouts.app')

@section('title', 'Iniciar Sesión - CorRol')

@section('content')
<div class="max-w-md mx-auto bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl">
    <h2 class="text-2xl font-bold text-indigo-400 mb-6 text-center">🔑 Iniciar Sesión</h2>

    @if ($errors->any())
        <div class="bg-rose-600/20 border border-rose-500 text-rose-300 p-3 rounded-lg mb-4 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Correo Electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" required 
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Contraseña</label>
            <input type="password" name="password" required 
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="flex items-center justify-between text-xs text-slate-400">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-indigo-600">
                <span>Recordarme</span>
            </label>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg shadow-lg transition-colors">
            Entrar
        </button>
    </form>

    <p class="text-center text-xs text-slate-400 mt-6">
        ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-indigo-400 hover:underline">Regístrate gratis</a>
    </p>
</div>
@endsection
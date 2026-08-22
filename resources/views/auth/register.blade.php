@extends('layouts.app')

@section('title', 'Registro de Usuario - CorRol')

@section('content')
<div class="max-w-md mx-auto bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl">
    <h2 class="text-2xl font-bold text-indigo-400 mb-6 text-center">📝 Crear Cuenta</h2>

    @if ($errors->any())
        <div class="bg-rose-600/20 border border-rose-500 text-rose-300 p-3 rounded-lg mb-4 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Nombre de Usuario</label>
            <input type="text" name="name" value="{{ old('name') }}" required 
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Correo Electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" required 
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Contraseña (Mínimo 8 caracteres)</label>
            <input type="password" name="password" required 
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Confirmar Contraseña</label>
            <input type="password" name="password_confirmation" required 
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg shadow-lg transition-colors">
            Registrarme
        </button>
    </form>

    <p class="text-center text-xs text-slate-300 mt-6">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-indigo-400 hover:underline">Inicia sesión</a>
    </p>
</div>
@endsection
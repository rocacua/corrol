@extends('layouts.app')

@section('title', 'Mi Perfil - CorRol')

@section('content')
<div class="max-w-md mx-auto space-y-6">

    <!-- Tarjeta de Acceso a Mis Recursos -->
    <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-xl flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-slate-100">📂 Mis Recursos</h3>
            <p class="text-xs text-slate-300">Total de recursos creados: {{ $user->resources()->count() }}</p>
        </div>
        <a href="{{ route('resources.index', ['user_id' => $user->id]) }}" 
           class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 rounded-lg shadow-md transition-colors">
            Ver Mis Recursos →
        </a>
    </div>

    <!-- Formulario de Edición de Perfil -->
    <div class="bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl">
        <h2 class="text-2xl font-bold text-indigo-400 mb-6">⚙️ Editar Mi Perfil</h2>

        @if ($errors->any())
            <div class="bg-rose-600/20 border border-rose-500 text-rose-300 p-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Correo Electrónico</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <hr class="border-slate-700 my-4">

            <div>
                <label class="block text-sm font-medium mb-1">Nueva Contraseña (Opcional)</label>
                <input type="password" name="password" placeholder="Dejar en blanco para mantener la actual" 
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmation" 
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg shadow-lg transition-colors">
                Guardar Cambios
            </button>
        </form>
    </div>
</div>
@endsection
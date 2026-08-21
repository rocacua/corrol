@extends('layouts.app')

@section('title', 'Acceso Restringido - CorRol')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    
    @include('partials.creation-nav')

    <div class="bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl text-center space-y-4">
        <div class="text-4xl">🔒</div>
        <h2 class="text-2xl font-bold text-indigo-400">Registro Requerido</h2>
        <p class="text-slate-300 text-sm">
            Para crear o editar <strong>{{ $type ?? 'recursos avanzados' }}</strong> (Fichas, Diarios, Mapas o Campañas) necesitas tener una cuenta de usuario.
        </p>
        <div class="flex justify-center gap-4 pt-4">
            <a href="{{ route('login') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-2.5 rounded-lg text-sm shadow">
                Iniciar Sesión
            </a>
            <a href="{{ route('register') }}" class="bg-slate-700 hover:bg-slate-600 text-white font-bold px-6 py-2.5 rounded-lg text-sm">
                Registrarse Gratis
            </a>
        </div>
    </div>
</div>
@endsection
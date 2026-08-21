@extends('layouts.app')

@section('title', 'Bienvenido a CorRol')

@section('content')
<div class="text-center py-16 space-y-6">
    <h1 class="text-5xl font-extrabold text-indigo-400 tracking-tight">🎲 CorRol</h1>
    <p class="text-xl text-slate-300 max-w-2xl mx-auto">
        Gestor Ágil de Campañas de Rol, Manuales, Fichas de PNJ, Diarios de Sesión y Mapas Interactivos.
    </p>
    <div class="pt-4 flex justify-center gap-4">
        <a href="{{ route('resources.index') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-8 py-3.5 rounded-xl shadow-lg transition-colors text-base">
            Explorar Recursos
        </a>
        <a href="{{ route('resources.create') }}" class="bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-bold px-8 py-3.5 rounded-xl shadow-lg transition-colors text-base">
            + Subir Recurso
        </a>
    </div>
</div>
@endsection
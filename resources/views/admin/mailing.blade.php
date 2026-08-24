@extends('layouts.app')

@section('title', 'Mailing Masivo - CorRol')

@section('content')
<div class="space-y-6">
    <h1 class="text-3xl font-bold text-indigo-400">✉️ Envío Masivo de Correos</h1>

    <!-- Filtro de Destinatarios -->
    <form method="GET" action="{{ route('admin.mailing') }}" class="bg-slate-800 p-6 rounded-xl border border-slate-700 space-y-4">
        <h3 class="text-lg font-bold text-slate-200">🔍 Filtrar Audiencia por Intereses</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Por Juego (Recursos o Favoritos):</label>
                <input type="text" name="game" value="{{ $filters['game'] ?? '' }}" placeholder="Ej: D&D, Pathfinder" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Por Etiqueta (#Tag):</label>
                <input type="text" name="tag" value="{{ $filters['tag'] ?? '' }}" placeholder="Ej: mapa, aventura" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Tipo de Recurso Creado:</label>
                <select name="has_resource_type" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                    <option value="">-- Todos los tipos --</option>
                    <option value="file" {{ ($filters['has_resource_type'] ?? '') === 'file' ? 'selected' : '' }}>Archivo</option>
                    <option value="sheet" {{ ($filters['has_resource_type'] ?? '') === 'sheet' ? 'selected' : '' }}>Ficha</option>
                    <option value="map" {{ ($filters['has_resource_type'] ?? '') === 'map' ? 'selected' : '' }}>Mapa</option>
                </select>
            </div>
        </div>
        <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-slate-100 text-xs font-bold px-4 py-2 rounded-lg">
            Aplicar Filtros
        </button>
    </form>

    <!-- Formulario de Envío -->
    <form method="POST" action="{{ route('admin.mailing.send') }}" class="bg-slate-800 p-6 rounded-xl border border-slate-700 space-y-4">
        @csrf
        <input type="hidden" name="game" value="{{ $filters['game'] ?? '' }}">
        <input type="hidden" name="tag" value="{{ $filters['tag'] ?? '' }}">
        <input type="hidden" name="has_resource_type" value="{{ $filters['has_resource_type'] ?? '' }}">

        <div class="text-xs text-indigo-300 font-semibold">
            📬 Destinatarios seleccionados: {{ $users->count() }} usuario(s)
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Asunto del Mensaje:</label>
            <input type="text" name="subject" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Cuerpo del Mensaje:</label>
            <textarea name="message" rows="6" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100"></textarea>
        </div>

        <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3 rounded-lg shadow">
            🚀 Enviar Correo a los {{ $users->count() }} Usuarios
        </button>
    </form>
</div>
@endsection
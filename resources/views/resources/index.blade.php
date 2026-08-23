@extends('layouts.app')

@section('title', 'Buscador de Recursos - CorRol')

@section('content')
<div class="space-y-8">
    
    <!-- Buscador Simple y Avanzado -->
    <form action="{{ route('resources.index') }}" method="GET" class="bg-slate-800 p-6 rounded-xl border border-slate-700 space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por título, descripción, juego, autor..."
                   class="w-full sm:flex-1 bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-3 rounded-lg transition-colors flex items-center justify-center gap-2 text-sm shrink-0">
                🔍 <span>Buscar</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-2 text-sm">
            <select name="game" class="bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-slate-300">
                <option value="">-- Todos los Juegos --</option>
                @foreach($games as $g)
                    <option value="{{ $g }}" {{ ($filters['game'] ?? '') === $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>

            <select name="campaign" class="bg-slate-900 border border-slate-700 rounded-lg p-2 text-slate-300">
                <option value="">-- Todas las Campañas --</option>
                @foreach($campaigns as $c)
                    <option value="{{ $c }}" {{ ($filters['campaign'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>

            <select name="author" class="bg-slate-900 border border-slate-700 rounded-lg p-2 text-slate-300">
                <option value="">-- Todos los Autores --</option>
                @foreach($authors as $a)
                    <option value="{{ $a }}" {{ ($filters['author'] ?? '') === $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>

            <a href="{{ route('resources.index') }}" class="text-slate-300 hover:text-slate-200 text-center self-center">
                Limpiar Filtros
            </a>
        </div>
    </form>

    <!-- Grid de Recursos -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($resources as $resource)
            <div class="bg-slate-800 rounded-xl p-5 border border-slate-700 flex flex-col justify-between hover:border-indigo-500 transition-colors">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs px-2.5 py-1 bg-indigo-600/30 text-indigo-300 rounded-full font-bold uppercase">
                            {{ $resource->resourceable->file_type ?? $resource->type }}
                        </span>
                        <span class="text-xs {{ $resource->privacy === 'private' ? 'text-amber-400' : 'text-emerald-400' }}">
                            {{ ucfirst($resource->privacy) }}
                        </span>
                        <span class="text-xs text-slate-300">
                            <strong class="text-slate-200">
                                {{ $resource->game ?? 'General' }}<br />{{ $resource->campaign ?? '' }}<br />{{ $resource->author ?? 'Anónimo' }}
                            </strong>
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-100 mb-1">
                        <a href="{{ route('resources.show', $resource->id) }}" class="hover:text-indigo-400">
                            {{ $resource->title }}
                        </a>
                    </h2>
                    <p class="text-slate-300 text-sm line-clamp-2 mb-4">{{ $resource->description ?? 'Sin descripción' }}</p>
                </div>

                <div class="pt-3 border-t border-slate-700/60 flex justify-between items-center text-xs text-slate-400">
                    <span>Subido por: <strong class="text-slate-300">{{ $resource->user->name ?? 'Anónimo' }}</strong></span>
                    <a href="{{ route('resources.show', $resource->id) }}" class="text-indigo-400 hover:underline font-bold">
                        Ver Recurso →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12 text-slate-400">
                No se encontraron recursos que coincidan con la búsqueda.
            </div>
        @endforelse
    </div>

    <div>
        {{ $resources->links() }}
    </div>

</div>
@endsection
@extends('layouts.app')

@section('title', 'Explorar Recursos - CorRol')

@section('content')
<div class="space-y-6">
    {{-- Formulario principal que engloba búsqueda rápida y avanzada --}}
    <form method="GET" action="{{ route('resources.index') }}" id="advanced-search" class="space-y-6">
        
        <!-- ENCABEZADO CON BÚSQUEDA GENERAL DE ACCESO RÁPIDO -->
        <div class="flex flex-wrap justify-between items-center gap-4 bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-xl">
            <div>
                <h1 class="text-3xl font-bold text-indigo-400 shrink-0">🎲 Explorador de Recursos</h1>
                @if($resources->total() > 0)
                    @php
                        $totalFavs = $resources->total_favorited ?? $resources->filter(fn($r) => ($r->favorited_by_count ?? 0) > 0)->count();
                    @endphp
                    <p class="text-xs text-slate-400 mt-1">
                        Encontrado <strong class="text-indigo-300">{{ $resources->total() }}</strong> recursos.
                        @if($totalFavs > 0)
                            <strong class="text-amber-400">{{ $totalFavs }}</strong> favoritos.
                        @endif
                    </p>
                @else
                    <p class="text-xs text-slate-400 mt-1">Explora la colección pública de CorRol.</p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3 md:w-auto flex-1 md:flex-initial justify-end">
                <!-- Campo Búsqueda General -->
                <div class="relative sm:w-64">
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="🔍 Buscar por cualquier campo..." 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-3 text-xs text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2 rounded-lg transition-colors shadow">
                    Buscar
                </button>

                <button type="button" onclick="document.getElementById('advanced-filters-panel').classList.toggle('hidden')" 
                        class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-bold px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                    ⚙️ Búsqueda Avanzada / Ordenar
                </button>
            </div>
        </div>

        <!-- PANEL DE BÚSQUEDA AVANZADA (DESPLEGABLE) -->
        <div id="advanced-filters-panel" 
              class="{{ !empty(array_filter(array_diff_key($filters, ['q' => '']))) ? '' : 'hidden' }} bg-slate-800 p-6 rounded-xl border border-slate-700 space-y-4 shadow-xl">
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Tipo de Recurso -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tipo de Recurso:</label>
                    <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                        <option value="">-- Todos los Tipos --</option>
                        <option value="file" {{ ($filters['type'] ?? '') === 'file' ? 'selected' : '' }}>Archivo / Documento</option>
                        <option value="sheet" {{ ($filters['type'] ?? '') === 'sheet' ? 'selected' : '' }}>Ficha de PJ</option>
                        <option value="diary" {{ ($filters['type'] ?? '') === 'diary' ? 'selected' : '' }}>Diario de Sesión</option>
                        <option value="campaign" {{ ($filters['type'] ?? '') === 'campaign' ? 'selected' : '' }}>Campaña</option>
                        <option value="map" {{ ($filters['type'] ?? '') === 'map' ? 'selected' : '' }}>Mapa Interactivo</option>
                    </select>
                </div>

                <!-- Juego -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Juego de Rol:</label>
                    <input type="text" name="game" list="games-list" value="{{ $filters['game'] ?? '' }}" placeholder="Ej: D&D 5e" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                    <datalist id="games-list">
                        @foreach($games as $g) <option value="{{ $g }}"> @endforeach
                    </datalist>
                </div>

                <!-- Campaña -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Campaña:</label>
                    <input type="text" name="campaign" list="campaigns-list" value="{{ $filters['campaign'] ?? '' }}" placeholder="Nombre de campaña" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                    <datalist id="campaigns-list">
                        @foreach($campaigns as $c) <option value="{{ $c }}"> @endforeach
                    </datalist>
                </div>

                <!-- Autor -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Autor Original:</label>
                    <input type="text" name="author" list="authors-list" value="{{ $filters['author'] ?? '' }}" placeholder="Nombre del autor" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                    <datalist id="authors-list">
                        @foreach($authors as $a) <option value="{{ $a }}"> @endforeach
                    </datalist>
                </div>

                <!-- Etiqueta (Tag) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Etiqueta (#Tag):</label>
                    <input type="text" name="tag" value="{{ $filters['tag'] ?? '' }}" placeholder="Ej: mapa" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                </div>

                <!-- Fecha Desde -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Publicado desde:</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                </div>

                <!-- Fecha Hasta -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Publicado hasta:</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                </div>

                <!-- Criterio de Ordenación -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Ordenar por:</label>
                    <select name="sort" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                        <option value="latest" {{ ($filters['sort'] ?? '') === 'latest' ? 'selected' : '' }}>Más recientes primero</option>
                        <option value="oldest" {{ ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' }}>Más antiguos primero</option>
                        <option value="affinity" {{ ($filters['sort'] ?? '') === 'affinity' ? 'selected' : '' }}>Afinidad (Favoritos y Propios primero)</option>
                        <option value="title_asc" {{ ($filters['sort'] ?? '') === 'title_asc' ? 'selected' : '' }}>Título (A-Z)</option>
                        <option value="title_desc" {{ ($filters['sort'] ?? '') === 'title_desc' ? 'selected' : '' }}>Título (Z-A)</option>
                        <option value="type" {{ ($filters['sort'] ?? '') === 'type' ? 'selected' : '' }}>Tipo de Recurso</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-slate-700/60">
                <a href="{{ route('resources.index') }}" class="bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs font-bold px-4 py-2 rounded-lg">
                    Limpiar Filtros
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-6 py-2 rounded-lg shadow">
                    🔍 Aplicar Filtros
                </button>
            </div>
        </div>
    </form>

    <!-- LISTADO DE RESULTADOS -->
    @if($resources->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($resources as $resource)
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 shadow-lg flex flex-col justify-between hover:border-indigo-500 transition-colors">
                    <div>
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <span class="px-2.5 py-0.5 bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-[10px] font-bold rounded-full uppercase shrink-0">
                                {{ $resource->type == 'file' ? $resource->resourceable->file_type : $resource->type }}
                            </span>
                            <span class="text-xs {{ $resource->privacy === 'private' ? 'text-amber-400' : 'text-emerald-400' }} shrink-0">
                                {{ ucfirst($resource->privacy) }}
                                <br /><span class="text-[10px] text-slate-400">{{ $resource->created_at->format('d/m/Y') }}</span>
                                @if(($resource->favorited_by_count ?? 0) > 0)
                                    <br /><span class="text-[10px] text-amber-400 font-bold" title="Favorito de {{ $resource->favorited_by_count }} usuarios">⭐ {{ $resource->favorited_by_count }}</span>
                                @endif
                            </span>
                            {{-- Bloque de Metadatos con Ancho Máximo y Truncado --}}
                            <span class="text-xs text-slate-300 min-w-0 max-w-[150px] sm:max-w-[180px] md:max-w-[200px] text-right">
                                <strong class="text-slate-200 block min-w-0">
                                    <span class="block truncate" title="🎲 {{ $resource->game ?? 'General' }}">🎲 {{ $resource->game ?? 'General' }}</span>
                                    @if($resource->campaign)
                                        <span class="block truncate mt-0.5" title="📜 {{ $resource->campaign }}">📜 {{ $resource->campaign }}</span>
                                    @endif
                                    <span class="block truncate mt-0.5" title="✍️ {{ $resource->author ?? 'Anónimo' }}">✍️ {{ $resource->author ?? 'Anónimo' }}</span>

                                </strong>
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-100 hover:text-indigo-400 transition-colors">
                            <a href="{{ route('resources.show', $resource->id) }}">{{ $resource->title }}</a>
                        </h3>
                        @if($resource->description)
                            <p class="text-slate-400 text-xs mt-2 line-clamp-2">{{ $resource->description }}</p>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
                        <span><strong class="text-slate-300" title="Subido por {{ $resource->user->name ?? 'Anónimo' }}"> {{ $resource->user->name ?? 'Anónimo' }}</strong></span>
                        <a href="{{ route('resources.show', $resource->id) }}" class="text-indigo-400 hover:underline font-semibold">
                            Ver Recurso →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $resources->links() }}
        </div>
    @else
        <div class="bg-slate-800 rounded-xl p-12 text-center border border-slate-700">
            <p class="text-slate-400">No se encontraron recursos con los filtros aplicados.</p>
        </div>
    @endif
</div>
@endsection
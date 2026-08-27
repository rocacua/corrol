@extends('layouts.app')

@section('title', 'Favoritos de ' . $targetUser->name . ' - CorRol')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap justify-between items-center gap-4 bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-xl">
        <div>
            <h1 class="text-2xl font-bold text-slate-100 flex items-center gap-2">
                ⭐ Recursos Favoritos de {{ $targetUser->name }}
            </h1>
            @if($resources->total() > 0)
                <p class="text-xs text-slate-400 mt-1">
                    Se han encontrado <strong class="text-indigo-300">{{ $resources->total() }}</strong> recursos guardados en la lista de favoritos de {{ $targetUser->name }}.
                </p>
            @else
                <p class="text-xs text-slate-400 mt-1">Este usuario no tiene recursos favoritos públicos en su colección.</p>
            @endif
        </div>

        <!-- Selector de Ordenación -->
        <form method="GET" action="{{ route('favorites.index', $targetUser->id) }}" class="flex items-center gap-2">
            <label for="sort" class="text-xs font-semibold text-slate-300">Ordenar por:</label>
            <select name="sort" id="sort" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700 rounded-lg text-xs p-2 text-slate-200 outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="latest" {{ ($filters['sort'] ?? '') === 'latest' ? 'selected' : '' }}>Más recientes primero</option>
                <option value="oldest" {{ ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' }}>Más antiguos primero</option>
                <option value="title_asc" {{ ($filters['sort'] ?? '') === 'title_asc' ? 'selected' : '' }}>Título (A-Z)</option>
                <option value="title_desc" {{ ($filters['sort'] ?? '') === 'title_desc' ? 'selected' : '' }}>Título (Z-A)</option>
            </select>
        </form>
    </div>

    <!-- Grid de Recursos -->
    @if($resources->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($resources as $resource)
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 shadow-lg flex flex-col justify-between hover:border-indigo-500 transition-colors">
                    <div>
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <span class="px-2.5 py-0.5 bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-[10px] font-bold rounded-full uppercase">
                                {{ $resource->type == 'file' ? $resource->resourceable->file_type : $resource->type }}
                            </span>
                            <span class="text-xs {{ $resource->privacy === 'private' ? 'text-amber-400' : 'text-emerald-400' }}">
                                {{ ucfirst($resource->privacy) }}
                                <br /><span class="text-[10px] text-slate-400">{{ $resource->created_at->format('d/m/Y') }}</span>
                                @if(($resource->favorited_by_count ?? 0) > 0)
                                    <br /><span class="text-[10px] text-amber-400 font-bold" title="Favorito de {{ $resource->favorited_by_count }} usuarios">⭐ {{ $resource->favorited_by_count }}</span>
                                @endif
                            </span>
                            <span class="text-xs text-slate-300">
                                <strong class="text-slate-200">
                                    🎲 {{ $resource->game ?? 'General' }}<br />
                                    📜 {{ $resource->campaign ?? '' }}<br />
                                    ✍️ {{ $resource->author ?? 'Anónimo' }}
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
                        <span>{{ $resource->game ?? 'General' }}</span>
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
            <p class="text-slate-400">Este usuario no tiene ningún recurso guardado en sus favoritos.</p>
        </div>
    @endif
</div>
@endsection
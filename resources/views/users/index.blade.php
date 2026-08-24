@extends('layouts.app')

@section('title', 'Usuarios y Autores - CorRol')

@section('content')
<div class="space-y-6">
    <h1 class="text-3xl font-bold text-indigo-400">👥 Creadores</h1>
    <p class="text-slate-300 text-sm">Listado de usuarios registrados en la plataforma.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @forelse($users as $user)
            <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 flex items-center justify-between gap-3 shadow-lg">
                {{-- Información del usuario (Izquierda) --}}
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <div class="truncate">
                        <h3 class="font-bold text-base text-slate-100 truncate">{{ $user->name }}</h3>
                        <p class="text-xs text-slate-300 truncate">{{ $user->resources_count }} {{ $user->resources_count === 1 ? 'recurso público' : 'recursos públicos' }}</p>
                    </div>
                </div>

                {{-- Botones apilados verticalmente (Derecha) --}}
                <div class="flex flex-col gap-1.5 shrink-0 text-xs font-bold">
                    <a href="{{ route('resources.index', ['user_id' => $user->id]) }}" class="bg-slate-700 hover:bg-indigo-600 text-slate-200 hover:text-white px-3 py-1.5 rounded-lg transition-colors text-center">
                        Recursos
                    </a>
                    <a href="{{ route('favorites.index', $user->id) }}" class="bg-slate-700 hover:bg-amber-600 text-slate-200 hover:text-white px-3 py-1.5 rounded-lg transition-colors text-center">
                        ⭐ Favoritos
                    </a>
                </div>
            </div>
        @empty
            <p class="text-slate-400 col-span-3 text-center py-8">No hay usuarios registrados.</p>
        @endforelse
    </div>
</div>
@endsection
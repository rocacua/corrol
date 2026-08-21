@extends('layouts.app')

@section('title', 'Usuarios y Autores - CorRol')

@section('content')
<div class="space-y-6">
    <h1 class="text-3xl font-bold text-indigo-400">👥 Autores y Creadores</h1>
    <p class="text-slate-400 text-sm">Listado de usuarios registrados en la plataforma.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @forelse($users as $user)
            <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 flex justify-between items-center shadow-lg">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-lg">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <div>
                        <h3 class="font-bold text-base text-slate-100">{{ $user->name }}</h3>
                        <p class="text-xs text-slate-400">{{ $user->resources_count }} {{ $user->resources_count === 1 ? 'recurso público' : 'recursos públicos' }}</p>
                    </div>
                </div>
                <a href="{{ route('resources.index', ['user_id' => $user->id]) }}" class="bg-slate-700 hover:bg-indigo-600 text-slate-200 hover:text-white font-bold text-xs px-3 py-2 rounded-lg transition-colors">
                    Ver Recursos →
                </a>
            </div>
        @empty
            <p class="text-slate-500 col-span-3 text-center py-8">No hay usuarios registrados.</p>
        @endforelse
    </div>
</div>
@endsection
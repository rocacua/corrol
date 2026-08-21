@extends('layouts.app')

@section('title', 'Campañas de Rol - CorRol')

@section('content')
<div class="space-y-6">
    <h1 class="text-3xl font-bold text-indigo-400">📜 Listado de Campañas</h1>
    <p class="text-slate-400 text-sm">Explora las campañas de rol creadas por la comunidad.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        @forelse($campaigns as $campaign)
            <a href="{{ route('resources.index', ['campaign' => $campaign]) }}" 
               class="bg-slate-800 p-6 rounded-xl border border-slate-700 hover:border-indigo-500 text-center font-bold text-lg text-slate-200 hover:text-indigo-400 transition-all shadow-md">
                📜 {{ $campaign }}
            </a>
        @empty
            <p class="text-slate-500 col-span-4 text-center py-8">Aún no hay campañas registradas.</p>
        @endforelse
    </div>
</div>
@endsection
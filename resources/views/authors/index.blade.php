@extends('layouts.app')

@section('title', 'Autores de Recursos - CorRol')

@section('content')
<div class="space-y-6">
    <h1 class="text-3xl font-bold text-indigo-400">✍️ Autores Originales</h1>
    <p class="text-slate-400 text-sm">Listado de autores originales de los materiales compartidos.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        @forelse($authors as $author)
            <a href="{{ route('resources.index', ['author' => $author]) }}" 
               class="bg-slate-800 p-6 rounded-xl border border-slate-700 hover:border-indigo-500 text-center font-bold text-lg text-slate-200 hover:text-indigo-400 transition-all shadow-md">
                ✍️ {{ $author }}
            </a>
        @empty
            <p class="text-slate-500 col-span-4 text-center py-8">Aún no hay autores registrados.</p>
        @endforelse
    </div>
</div>
@endsection
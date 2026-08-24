@extends('layouts.app')

@section('title', 'Panel de Control Secreto - CorRol')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-xl">
        <div>
            <h1 class="text-3xl font-bold text-indigo-400">🛡️ Panel de Administración</h1>
            <p class="text-slate-300 text-sm mt-1">Estadísticas globales de CorRol.</p>
        </div>
        <a href="{{ route('admin.mailing') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-2 rounded-lg text-sm shadow">
            ✉️ Mailings Masivos
        </a>
    </div>

    <!-- Metadatos de Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
            <span class="text-slate-400 text-xs font-semibold">Usuarios Totales</span>
            <p class="text-3xl font-bold text-slate-100 mt-1">{{ $stats['total_users'] }}</p>
        </div>
        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
            <span class="text-slate-400 text-xs font-semibold">Recursos Totales</span>
            <p class="text-3xl font-bold text-slate-100 mt-1">{{ $stats['total_resources'] }}</p>
        </div>
        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
            <span class="text-slate-400 text-xs font-semibold">Recursos Públicos / Privados</span>
            <p class="text-3xl font-bold text-emerald-400 mt-1">{{ $stats['public_resources'] }} <span class="text-slate-500 text-lg">/ {{ $stats['private_resources'] }}</span></p>
        </div>
        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
            <span class="text-slate-400 text-xs font-semibold">Espacio Ocupado (B2)</span>
            <p class="text-3xl font-bold text-indigo-400 mt-1">{{ number_format($stats['total_storage_bytes'] / (1024 * 1024), 2) }} MB</p>
        </div>
    </div>
</div>
@endsection
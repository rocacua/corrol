@extends('layouts.app')

@section('title', 'Subir Recurso - CorRol')

@section('content')
<div class="max-w-3xl mx-auto bg-slate-800 rounded-xl p-8 shadow-2xl border border-slate-700">
    
    <!-- BARRA DE NAVEGACIÓN ENTRE TIPOS DE CREACIÓN -->
    @include('partials.creation-nav')
    
    <h1 class="text-3xl font-bold mb-2 text-indigo-400">🎲 Subir / Registrar Recurso</h1>
    <p class="text-slate-300 mb-6">Añade manuales, imágenes, audios, mapas o enlaces para tus campañas de rol.</p>

    <!-- Indicador de Almacenamiento -->
    @php
        if ($usedBytes >= 1024 * 1024) {
            $formattedUsed = number_format($usedBytes / (1024 * 1024), 2) . ' MB';
        } else {
            $formattedUsed = number_format($usedBytes / 1024, 2) . ' KB';
        }
        $remainingMB = round($remainingBytes / (1024 * 1024), 2);
        $percentageUsed = min(100, round(($usedBytes / $totalLimitBytes) * 100, 2));
        $isLowStorage = $remainingMB < 100;
        $barWidth = max(0.5, $percentageUsed) . '%';
    @endphp

    <div class="mb-6 p-4 rounded-lg bg-slate-900 border border-slate-700">
        <div class="flex justify-between items-center text-xs mb-1">
            <span class="text-slate-300">Espacio en la Nube (Compartido):</span>
            <span class="font-semibold {{ $isLowStorage ? 'text-rose-400' : 'text-slate-300' }}">
                {{ $formattedUsed }} de 10,240 MB usados (Quedan {{ $remainingMB }} MB)
            </span>
        </div>
        <div class="w-full bg-slate-700 h-2.5 rounded-full overflow-hidden">
            <div id="storage-progress-bar" class="h-2.5 rounded-full {{ $isLowStorage ? 'bg-rose-500' : 'bg-indigo-500' }}" data-width="{{ max(0.5, $percentageUsed) }}"></div>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-rose-600/20 border border-rose-500 text-rose-300 p-4 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('warning'))
        <div class="bg-amber-600/20 border border-amber-500 text-amber-300 p-4 rounded-lg mb-6 flex justify-between items-center">
            <div>
                <strong>⚠️ Aviso:</strong> {{ session('warning') }}
            </div>
            @if (session('existing_resource_id'))
                <a href="{{ route('resources.show', session('existing_resource_id')) }}" 
                   class="bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs px-4 py-2 rounded-lg shadow transition-colors">
                    Ver "{{ session('existing_resource_title') }}" &rarr;
                </a>
            @endif
        </div>
    @endif
    
    @if (session('success'))
        <div class="bg-emerald-600/20 border border-emerald-500 text-emerald-300 p-4 rounded-lg mb-6 flex flex-wrap justify-between items-center gap-4">
            <div>
                <strong class="text-lg">¡Recurso registrado con éxito!</strong>
                @if(session('resource_title'))
                    <div class="text-sm mt-1">Recurso: <em>{{ session('resource_title') }}</em></div>
                @endif
            </div>
            @if(session('resource_id'))
                <a href="{{ route('resources.show', session('resource_id')) }}" 
                   class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm px-4 py-2.5 rounded-lg shadow transition-colors">
                    Ver Recurso Creado &rarr;
                </a>
            @endif
        </div>
    @endif
    
    <form
        id="resource-upload-form"
        action="{{ route('resources.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf

        <div>
            <label for="title-input" class="block text-sm font-medium mb-2">Título del Recurso *</label>
            <input type="text" id="title-input" name="title" value="{{ old('title') }}" required 
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <div>
            <label for="desc-input" class="block text-sm font-medium mb-2">Descripción</label>
            <textarea id="desc-input" name="description" rows="3" 
                      class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="game-input" class="block text-sm font-medium mb-2">Juego de Rol</label>
                <input type="text" id="game-input" name="game" list="games-list" placeholder="Ej: RuneQuest, D&D 5e" value="{{ old('game') }}"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                <datalist id="games-list">
                    @foreach ($games as $g)
                        <option value="{{ $g }}">
                    @endforeach
                </datalist>
            </div>

            <div>
                <label for="campaign-input" class="block text-sm font-medium mb-2">Campaña</label>
                <input type="text" id="campaign-input" name="campaign" list="campaigns-list" placeholder="Ej: Juego de Dioses" value="{{ old('campaign') }}"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                <datalist id="campaigns-list">
                    @foreach ($campaigns as $c)
                        <option value="{{ $c }}">
                    @endforeach
                </datalist>
            </div>

            <div>
                <label for="author-input" class="block text-sm font-medium mb-2">Autor Original</label>
                <input type="text" id="author-input" name="author" list="authors-list" placeholder="Ej: Greg Stafford" value="{{ old('author') }}"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                <datalist id="authors-list">
                    @foreach ($authors as $a)
                        <option value="{{ $a }}">
                    @endforeach
                </datalist>
            </div>

            <div>
                <label for="privacy-select" class="block text-sm font-medium mb-2">Privacidad</label>
                @auth
                    <select id="privacy-select" name="privacy" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="public" {{ old('privacy') === 'public' ? 'selected' : '' }}>Público (Todos pueden verlo)</option>
                        <option value="private" {{ old('privacy') === 'private' ? 'selected' : '' }}>Privado (Solo accesible para mí)</option>
                    </select>
                @else
                    <input type="hidden" name="privacy" value="public">
                    <div class="bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-300 text-sm flex justify-between items-center">
                        <span>🌐 Público (Sin registro)</span>
                        <a href="{{ route('login') }}" class="text-xs text-indigo-400 hover:underline">Inicia sesión para privado</a>
                    </div>
                @endauth
            </div>
        </div>

        <div>
            <label for="tags-input" class="block text-sm font-medium mb-2">Palabras Clave (Separadas por comas)</label>
            <input type="text" id="tags-input" name="tags" placeholder="Ej: mapa, ciudad, pnj, pdf" value="{{ old('tags') }}"
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
            
            @if(!empty($allTags))
                <div class="mt-2 flex flex-wrap gap-2 items-center text-xs text-slate-300">
                    <span class="font-semibold text-slate-400">Sugerencias:</span>
                    @foreach ($allTags as $tag)
                        <button type="button" data-tag="{{ $tag }}" 
                                class="tag-suggestion-btn bg-slate-700 hover:bg-indigo-600 text-slate-300 hover:text-white px-2 py-1 rounded-md transition-colors cursor-pointer">
                            + {{ $tag }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <hr class="border-slate-700 my-6">

        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-indigo-300">Origen del Recurso</h3>
            
            <div>
                <label for="file-input" class="block text-sm font-medium mb-2">Opción 1: Subir Archivo (PDF, Imagen, ZIP, Audio, Video)</label>
                <input type="file" id="file-input" name="file" 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
            </div>

            <div class="text-center text-xs text-slate-400 font-bold">- O BIEN -</div>

            <div>
                <label for="url-input" class="block text-sm font-medium mb-2">Opción 2: Introducir Enlace / URL Externa</label>
                <input type="url" id="url-input" name="external_url" placeholder="https://ejemplo.com/recurso.pdf" value="{{ old('external_url') }}"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
        </div>

        <button
            id="resource-submit-button"
            type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-600 disabled:cursor-not-allowed disabled:opacity-75 text-white font-bold py-3 px-6 rounded-lg transition-colors shadow-lg"
        >
            Guardar Recurso
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Asignación de ancho de barra de progreso sin alertas de CSS
        const progressBar = document.getElementById('storage-progress-bar');
        if (progressBar) {
            const widthVal = progressBar.getAttribute('data-width') || '0.5';
            progressBar.style.width = widthVal + '%';
        }

        const input = document.getElementById('tags-input');
        const buttons = document.querySelectorAll('.tag-suggestion-btn');

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                const tagToAppend = this.getAttribute('data-tag');
                if (!tagToAppend || !input) return;

                let currentValues = input.value.split(',').map(function (t) {
                    return t.trim();
                }).filter(function (t) {
                    return t.length > 0;
                });

                if (!currentValues.includes(tagToAppend)) {
                    currentValues.push(tagToAppend);
                    input.value = currentValues.join(', ');
                }
            });
        });

        const uploadForm = document.getElementById('resource-upload-form');
        const submitButton = document.getElementById('resource-submit-button');

        if (uploadForm && submitButton) {
            let isSubmitting = false;

            uploadForm.addEventListener('submit', function (event) {
                if (isSubmitting) {
                    event.preventDefault();
                    return;
                }

                isSubmitting = true;
                submitButton.disabled = true;
                submitButton.textContent = 'Subiendo recurso...';
            });
        }
    });
</script>
@endsection
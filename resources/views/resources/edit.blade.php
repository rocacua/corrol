@extends('layouts.app')

@section('title', 'Editar: ' . $resource->title . ' - CorRol')

@section('content')
<div class="max-w-3xl mx-auto bg-slate-800 rounded-xl p-8 shadow-2xl border border-slate-700">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-indigo-400">✏️ Editar Recurso</h1>
             <p class="text-slate-400 text-sm">Modifica los metadatos o reemplaza el archivo/URL.</p>
        </div>
        <a href="{{ route('resources.show', $resource->id) }}" class="text-slate-400 hover:text-slate-200 text-sm">
            ← Cancelar
        </a>
    </div>

    <!-- Alerta si el recurso no tiene propietario (Adopción de Autoría) -->
    @if ($resource->user_id === null)
        <div class="bg-amber-600/20 border border-amber-500 text-amber-300 p-4 rounded-lg mb-6 flex items-start gap-3">
            <span class="text-2xl">⚠️</span>
            <div class="text-sm">
                <strong class="font-bold">Aviso de Adopción de Recurso:</strong>
                <p class="mt-1">Este recurso fue creado de forma anónima. Al guardar los cambios, <u class="font-semibold">pasará a pertenecer a tu cuenta</u> (<strong>{{ auth()->user()->name }}</strong>), por lo que en el futuro solo tú podrás volver a editarlo o eliminarlo.</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-rose-600/20 border border-rose-500 text-rose-300 p-4 rounded-lg mb-6 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

     <form action="{{ route('resources.update', $resource->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Título -->
        <div>
            <label class="block text-sm font-medium mb-2">Título del Recurso *</label>
            <input type="text" name="title" value="{{ old('title', $resource->title) }}" required 
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <!-- Descripción -->
        <div>
            <label class="block text-sm font-medium mb-2">Descripción</label>
            <textarea name="description" rows="3" 
                      class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">{{ old('description', $resource->description) }}</textarea>
        </div>

        <!-- Juego, Campaña, Autor y Privacidad -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Juego de Rol</label>
                <input type="text" name="game" list="games-list" placeholder="Ej: RuneQuest" value="{{ old('game', $resource->game) }}"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                <datalist id="games-list">
                    @foreach ($games as $g)
                        <option value="{{ $g }}">
                    @endforeach
                </datalist>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Campaña</label>
                <input type="text" name="campaign" list="campaigns-list" placeholder="Ej: Juego de Dioses" value="{{ old('campaign', $resource->campaign) }}"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                <datalist id="campaigns-list">
                    @foreach ($campaigns as $c)
                        <option value="{{ $c }}">
                    @endforeach
                </datalist>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Autor Original</label>
                <input type="text" name="author" list="authors-list" placeholder="Ej: Greg Stafford" value="{{ old('author', $resource->author) }}"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                <datalist id="authors-list">
                    @foreach ($authors as $a)
                        <option value="{{ $a }}">
                    @endforeach
                </datalist>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Privacidad</label>
                <select name="privacy" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="public" {{ old('privacy', $resource->privacy) === 'public' ? 'selected' : '' }}>Público (Todos pueden verlo)</option>
                    <option value="private" {{ old('privacy', $resource->privacy) === 'private' ? 'selected' : '' }}>Privado (Solo accesible para mí)</option>
                </select>
            </div>
        </div>

        <!-- Palabras Clave -->
        @php
            $currentTags = is_array($resource->tags) ? implode(', ', $resource->tags) : '';
        @endphp
        <div>
            <label class="block text-sm font-medium mb-2">Palabras Clave (Separadas por comas)</label>
            <input type="text" id="tags-input" name="tags" placeholder="Ej: mapa, ciudad, pnj" value="{{ old('tags', $currentTags) }}"
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
            
            @if(!empty($allTags))
                <div class="mt-2 flex flex-wrap gap-2 items-center text-xs text-slate-400">
                    <span class="font-semibold text-slate-500">Sugerencias:</span>
                    @foreach ($allTags as $tag)
                        <button type="button" onclick="appendTag('{{ $tag }}')" 
                                class="bg-slate-700 hover:bg-indigo-600 text-slate-300 hover:text-white px-2 py-1 rounded-md transition-colors cursor-pointer">
                            + {{ $tag }}
                        </button>
                    @endforeach
                </div>
            @endif


            </div>

        <!-- REEMPLAZO O CAMBIO DE ARCHIVO / URL -->
        <hr class="border-slate-700 my-6">

        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-indigo-300">Cambiar Archivo o URL Externa (Opcional)</h3>

            <!-- Información del Archivo / URL Actual -->
            @if($resource->type === 'file' && $resource->resourceable)
                @php $file = $resource->resourceable; @endphp
                <div class="p-3 bg-slate-900 rounded-lg border border-slate-700 text-xs space-y-1">
                    <span class="text-slate-400 font-semibold block">Origen Actual:</span>
                    @if($file->is_external)
                        <p class="text-indigo-300 break-all">🌐 Enlace Externo: {{ $file->file_path_or_url }}</p>
                    @else
                        <p class="text-emerald-300">📁 Archivo almacenado en la nube (Backblaze B2)</p>
                        @if($file->size_in_bytes)
                            <p class="text-slate-400">Tamaño: {{ number_format($file->size_in_bytes / 1024, 1) }} KB</p>
                        @endif
                    @endif
                </div>
            @endif

            <!-- Explicación de Prioridad y Borrado -->
            <div class="p-3 bg-indigo-950/40 border border-indigo-800/50 rounded-lg text-xs text-indigo-200">
                ℹ️ <strong>Notas de reemplazo:</strong>
                <ul class="list-disc list-inside mt-1 space-y-0.5 text-indigo-300/80">
                    <li>Si introduces una nueva <strong>URL externa</strong>, tendrá prioridad sobre la subida de archivo.</li>
                    <li>Si subes un <strong>nuevo archivo</strong> o cambias a URL, el archivo anterior en la nube se eliminará automáticamente.</li>
                    <li>Si dejas ambos campos en blanco, se conservará el archivo o URL actual.</li>
                </ul>
            </div>

            <!-- Opción 1: Nuevo Archivo -->
            <div>
                <label class="block text-sm font-medium mb-1">Subir Nuevo Archivo (Reemplazará al actual)</label>
                <input type="file" name="file" 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer text-sm">
            </div>

            <div class="text-center text-xs text-slate-500 font-bold">— Ó —</div>

            <!-- Opción 2: Nueva URL Externa -->
            <div>
                <label class="block text-sm font-medium mb-1">Nueva URL Externa (Prioritaria)</label>
                <input type="url" name="external_url" placeholder="https://ejemplo.com/nuevo_recurso.pdf" value="{{ old('external_url') }}"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            </div>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg transition-colors shadow-lg">
            Guardar Cambios
        </button>
    </form>
</div>

<script>
    function appendTag(tagToAppend) {
        const input = document.getElementById('tags-input');
        let currentValues = input.value.split(',').map(t => t.trim()).filter(t => t.length > 0);
        if (!currentValues.includes(tagToAppend)) {
            currentValues.push(tagToAppend);
            input.value = currentValues.join(', ');
        }
    }
</script>
@endsection
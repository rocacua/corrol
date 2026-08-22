@extends('layouts.app')

@section('title', 'Crear Mapa Interactivo - CorRol')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    
    @include('partials.creation-nav')

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- LISTA DE MAPAS DEL USUARIO -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 shadow-lg">
                <h3 class="font-bold text-sm text-indigo-400 mb-3 flex justify-between items-center">
                    <span>Mis Mapas</span>
                    <a href="{{ route('maps.create') }}" class="text-xs text-emerald-400 hover:underline">+ Nuevo</a>
                </h3>

                <div class="space-y-2 max-h-[500px] overflow-y-auto">
                    @forelse($userResources as $res)
                        <a href="?edit_id={{ $res->id }}" 
                           class="block p-3 rounded-lg border text-xs transition-all {{ ($editingResource && $editingResource->id === $res->id) ? 'bg-indigo-950 border-indigo-500 text-white font-bold' : 'bg-slate-900 border-slate-700/60 text-slate-300 hover:border-indigo-500' }}">
                            <div class="truncate">🗺️ {{ $res->title }}</div>
                            <div class="text-[10px] text-slate-400 mt-1">{{ $res->created_at->format('d/m/Y') }}</div>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 italic">No tienes ningún mapa aún.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- FORMULARIO DE MAPA INTERACTIVO -->
        <div class="lg:col-span-3 bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl space-y-6">
            <h1 class="text-2xl font-bold text-indigo-400">
                @if(!empty($isClone))
                    📋 Clonar Mapa Interactivo (Nueva Copia)
                @elseif($editingResource)
                    ✏️ Editar Mapa Interactivo
                @else
                    🗺️ Crear Mapa Interactivo
                @endif
            </h1>

            <form id="map-form" action="{{ route('maps.store') }}" method="POST" class="space-y-6">
                @csrf
                @if($editingResource && empty($isClone))
                    <input type="hidden" name="resource_id" value="{{ $editingResource->id }}">
                @endif
                <input type="hidden" name="markers_json" id="markers_json" value="[]">

                @php
                    $defaultMapTitle = $editingResource ? (!empty($isClone) ? '[Copia] ' . $editingResource->title : $editingResource->title) : '';
                @endphp

                <div>
                    <label class="block text-sm font-medium mb-2">Título del Mapa *</label>
                    <input type="text" name="title" value="{{ old('title', $defaultMapTitle) }}" required
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Juego de Rol</label>
                        <input type="text" name="game" list="games-list" placeholder="Ej: RuneQuest" value="{{ old('game', $editingResource->game ?? '') }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                        <datalist id="games-list">
                            @foreach ($games as $g) <option value="{{ $g }}"> @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Campaña</label>
                        <input type="text" name="campaign" list="campaigns-list" placeholder="Ej: Juego de Dioses" value="{{ old('campaign', $editingResource->campaign ?? '') }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                        <datalist id="campaigns-list">
                            @foreach ($campaigns as $c) <option value="{{ $c }}"> @endforeach
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">URL de la Imagen del Mapa *</label>
                    <input type="url" id="map_image_url" name="map_image_url" value="{{ old('map_image_url', $editingResource && $editingResource->resourceable ? $editingResource->resourceable->map_image_url : '') }}" required placeholder="https://ejemplo.com/mapa_continente.jpg"
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500"
                           onchange="loadMapImage()">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Privacidad</label>
                    <select name="privacy" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none">
                        <option value="public" {{ old('privacy', $editingResource->privacy ?? 'public') === 'public' ? 'selected' : '' }}>Público</option>
                        <option value="private" {{ old('privacy', $editingResource->privacy ?? 'public') === 'private' ? 'selected' : '' }}>Privado</option>
                    </select>
                </div>

                <!-- LIENZO INTERACTIVO DE MAPA -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium">Previsualización del Mapa (Haz clic para añadir un Pin)</label>
                        <span id="pin-count" class="text-xs text-indigo-400 font-bold">0 Pines añadidos</span>
                    </div>
                    <div id="map-canvas" data-markers='{!! json_encode($editingResource && $editingResource->resourceable ? ($editingResource->resourceable->markers ?? []) : []) !!}' class="w-full h-[450px] bg-slate-900 border border-slate-700 rounded-lg overflow-hidden relative">
                        <div id="map-placeholder" class="h-full flex items-center justify-center text-slate-400 text-sm">
                            Introduce una URL de imagen de mapa arriba para cargar el lienzo interactivo.
                        </div>
                    </div>
                </div>

                <button type="button" onclick="submitMapForm()" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg transition-colors shadow-lg">
                    Guardar Mapa Interactivo
                </button>
            </form>
        </div>

    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let mapInstance = null;
    const mapCanvasEl = document.getElementById('map-canvas');
    let markersList = JSON.parse(mapCanvasEl.getAttribute('data-markers') || '[]');

    function loadMapImage() {
        const url = document.getElementById('map_image_url').value;
        if (!url) return;

        document.getElementById('map-placeholder').style.display = 'none';

        if (mapInstance) {
            mapInstance.remove();
        }

        const img = new Image();
        img.src = url;
        img.onload = function () {
            const h = img.height;
            const w = img.width;

            mapInstance = L.map('map-canvas', {
                crs: L.CRS.Simple,
                minZoom: -2
            });

            const bounds = [[0, 0], [h, w]];
            L.imageOverlay(url, bounds).addTo(mapInstance);
            mapInstance.fitBounds(bounds);

            markersList.forEach(function(m) {
                L.marker([m.y, m.x]).addTo(mapInstance).bindPopup('<b>' + (m.label || '') + '</b><br>' + (m.description || ''));
            });
            updatePinCount();

            mapInstance.on('click', function (e) {
                const label = prompt('Título para este lugar/pin:');
                if (!label) return;
                const description = prompt('Descripción del lugar:') || '';

                const markerObj = { x: e.latlng.lng, y: e.latlng.lat, label: label, description: description };
                markersList.push(markerObj);

                L.marker([e.latlng.lat, e.latlng.lng]).addTo(mapInstance).bindPopup('<b>' + label + '</b><br>' + description);
                updatePinCount();
            });
        };
    }

    function updatePinCount() {
        document.getElementById('pin-count').innerText = markersList.length + ' Pines añadidos';
    }

    function submitMapForm() {
        document.getElementById('markers_json').value = JSON.stringify(markersList);
        document.getElementById('map-form').submit();
    }

    window.onload = function() {
        if (document.getElementById('map_image_url').value) {
            loadMapImage();
        }
    };
</script>
@endsection
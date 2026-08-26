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

                <!-- Descripción del Mapa -->
                <div>
                    <label class="block text-sm font-medium mb-2">Descripción del Mapa</label>
                    <textarea name="description" rows="3" placeholder="Breve descripción del contenido o región de este mapa..."
                              class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $editingResource->description ?? '') }}</textarea>
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
                    <label class="block text-sm font-medium mb-2">Privacidad</label>
                    <select name="privacy" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none">
                        <option value="public" {{ old('privacy', $editingResource->privacy ?? 'public') === 'public' ? 'selected' : '' }}>Público</option>
                        <option value="private" {{ old('privacy', $editingResource->privacy ?? 'public') === 'private' ? 'selected' : '' }}>Privado</option>
                    </select>
                </div>


                <!-- Etiquetas (Tags) -->
                <div>
                    <label class="block text-sm font-medium mb-2">Etiquetas (Separadas por comas)</label>
                    <input type="text" id="tags-input" name="tags" placeholder="ej: mapa, continente, mundo, mazmorra"
                           value="{{ old('tags', isset($editingResource->tags) ? implode(', ', $editingResource->tags) : '') }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                    
                    @if(!empty($allTags))
                    <div  class="mt-2 max-h-[60px] overflow-y-auto block">
                        <div class="mt-2 flex flex-wrap gap-2 items-center text-xs text-slate-300">
                            <span class="font-semibold text-slate-400">Sugerencias:</span>
                            @foreach ($allTags as $tag)
                                <button type="button" data-tag="{{ $tag }}" 
                                        class="tag-suggestion-btn bg-slate-700 hover:bg-indigo-600 text-slate-300 hover:text-white px-2 py-1 rounded-md transition-colors cursor-pointer">
                                    + {{ $tag }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">URL de la Imagen del Mapa *</label>
                    <input type="url" id="map_image_url" name="map_image_url" value="{{ old('map_image_url', $editingResource && $editingResource->resourceable ? $editingResource->resourceable->map_image_url : '') }}" required placeholder="https://ejemplo.com/mapa_continente.jpg"
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500"
                           onchange="loadMapImage()">
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
                    
                    {{-- Lista interactiva para gestionar pines --}}
                    <div id="pins-list-container" class="mt-4 hidden space-y-2">
                        <h4 class="text-xs font-bold text-slate-300">Pines en este mapa:</h4>
                        <div id="pins-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto"></div>
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
    let leafletMarkers = [];

    function loadMapImage() {
        const url = document.getElementById('map_image_url').value;
        if (!url) return;

        document.getElementById('map-placeholder').style.display = 'none';

        if (mapInstance) {
            mapInstance.remove();
            leafletMarkers = [];
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

            // Cargar pines existentes
            markersList.forEach(function(m, index) {
                createLeafletMarker(m, index);
            });
            renderPinsList();

            // Evento para añadir nuevo pin
            mapInstance.on('click', function (e) {
                const label = prompt('Título para este lugar/pin:');
                if (!label) return;
                const description = prompt('Descripción del lugar:') || '';

                const markerObj = { x: e.latlng.lng, y: e.latlng.lat, label: label, description: description };
                markersList.push(markerObj);

                const newIndex = markersList.length - 1;
                createLeafletMarker(markerObj, newIndex);
                renderPinsList();
            });
        };
    }

    function createLeafletMarker(m, index) {
        const marker = L.marker([m.y, m.x], { draggable: true }).addTo(mapInstance);

        // Actualizar coordenadas al arrastrar el pin
        marker.on('dragend', function(e) {
            const latlng = e.target.getLatLng();
            markersList[index].x = latlng.lng;
            markersList[index].y = latlng.lat;
        });

        updateMarkerPopup(marker, index);
        leafletMarkers[index] = marker;
    }

    function updateMarkerPopup(marker, index) {
        const m = markersList[index];
        const popupContent = `
            <div class="text-slate-900 p-1">
                <b class="text-sm">${m.label}</b><br>
                <span class="text-xs text-slate-600">${m.description || 'Sin descripción'}</span>
                <div class="mt-2 flex gap-2 border-t pt-2">
                    <button type="button" onclick="editPin(${index})" class="bg-amber-600 text-white text-[10px] font-bold px-2 py-1 rounded">✏️ Editar</button>
                    <button type="button" onclick="deletePin(${index})" class="bg-rose-600 text-white text-[10px] font-bold px-2 py-1 rounded">🗑️ Eliminar</button>
                </div>
            </div>
        `;
        marker.bindPopup(popupContent);
    }

    function editPin(index) {
        const m = markersList[index];
        const newLabel = prompt('Nuevo título para el pin:', m.label);
        if (newLabel === null) return;
        const newDesc = prompt('Nueva descripción:', m.description);

        markersList[index].label = newLabel || m.label;
        markersList[index].description = newDesc !== null ? newDesc : m.description;

        updateMarkerPopup(leafletMarkers[index], index);
        leafletMarkers[index].openPopup();
        renderPinsList();
    }

    function deletePin(index) {
        if (!confirm('¿Seguro que deseas eliminar este pin?')) return;

        mapInstance.removeLayer(leafletMarkers[index]);
        leafletMarkers.splice(index, 1);
        markersList.splice(index, 1);

        // Reindexar marcas restantes
        reloadAllMarkers();
    }

    function reloadAllMarkers() {
        leafletMarkers.forEach(m => mapInstance.removeLayer(m));
        leafletMarkers = [];

        markersList.forEach((m, index) => {
            createLeafletMarker(m, index);
        });
        renderPinsList();
    }

    function renderPinsList() {
        const container = document.getElementById('pins-list-container');
        const listEl = document.getElementById('pins-list');
        document.getElementById('pin-count').innerText = markersList.length + ' Pines añadidos';

        if (markersList.length === 0) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        listEl.innerHTML = '';

        markersList.forEach((m, index) => {
            const item = document.createElement('div');
            item.className = 'bg-slate-900 border border-slate-700 p-2.5 rounded-lg flex justify-between items-center text-xs';
            item.innerHTML = `
                <div class="truncate pr-2">
                    <span class="font-bold text-slate-200">📍 ${m.label}</span>
                    <p class="text-[10px] text-slate-400 truncate">${m.description || 'Sin descripción'}</p>
                </div>
                <div class="flex gap-1 shrink-0">
                    <button type="button" onclick="editPin(${index})" class="text-amber-400 hover:text-amber-300 px-1.5 py-0.5">✏️</button>
                    <button type="button" onclick="deletePin(${index})" class="text-rose-400 hover:text-rose-300 px-1.5 py-0.5">🗑️</button>
                </div>
            `;
            listEl.appendChild(item);
        });
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
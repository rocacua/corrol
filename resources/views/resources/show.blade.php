@extends('layouts.app')

@section('title', $resource->title . ' - CorRol')

@section('content')
<div class="space-y-6">
    
    <!-- Encabezado del Recurso -->
    <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-xl">
        <div class="flex flex-wrap justify-between items-start gap-4">
            <div>
                <span class="inline-block px-3 py-1 bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-xs font-bold rounded-full mb-2 uppercase">
                    {{ $resource->resourceable->file_type ?? $resource->type }}
                </span>
                <h1 class="text-3xl font-bold text-slate-100">{{ $resource->title }}</h1>
                @if($resource->description)
                    <p class="text-slate-300 mt-2">{{ $resource->description }}</p>
                @endif
            </div>

            @if($fileUrl)
                <a href="{{ $fileUrl }}" download target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3 rounded-lg shadow-lg">
                    ⬇️ Descargar Recurso
                </a>
            @endif
        </div>

        <!-- Metadatos Identificativos -->
        <div class="mt-6 pt-4 border-t border-slate-700/60 grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
            <div>
                <span class="block text-slate-400 text-xs">Juego:</span>
                @if($resource->game)
                    <a href="{{ route('resources.index', ['game' => $resource->game]) }}" class="font-semibold text-indigo-400 hover:underline">
                        🎲 {{ $resource->game }}
                    </a>
                @else
                    <span class="font-semibold text-slate-300">General</span>
                @endif
            </div>

            <div>
                <span class="block text-slate-400 text-xs">Campaña:</span>
                @if($resource->campaign)
                    <a href="{{ route('resources.index', ['campaign' => $resource->campaign]) }}" class="font-semibold text-indigo-400 hover:underline">
                        📜 {{ $resource->campaign }}
                    </a>
                @else
                    <span class="font-semibold text-slate-300">No especificada</span>
                @endif
            </div>

            <div>
                <span class="block text-slate-400 text-xs">Autor Original:</span>
                @if($resource->author)
                    <a href="{{ route('resources.index', ['author' => $resource->author]) }}" class="font-semibold text-indigo-400 hover:underline">
                        ✍️ {{ $resource->author }}
                    </a>
                @else
                    <span class="font-semibold text-slate-300">Desconocido</span>
                @endif
            </div>

            <div>
                <span class="block text-slate-400 text-xs">Subido por:</span>
                @if($resource->user)
                    <a href="{{ route('resources.index', ['user_id' => $resource->user_id]) }}" class="font-semibold text-indigo-400 hover:underline">
                        👤 {{ $resource->user->name }}
                    </a>
                @else
                    <span class="font-semibold text-slate-300">🌐 Anónimo</span>
                @endif
            </div>

            <div>
                <span class="block text-slate-400 text-xs">Privacidad:</span>
                <span class="font-semibold {{ $resource->privacy === 'private' ? 'text-amber-400' : 'text-emerald-400' }}">
                    {{ ucfirst($resource->privacy) }}
                </span>
            </div>
        </div>

        @if(!empty($resource->tags))
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($resource->tags as $tag)
                    <a href="{{ route('resources.index', ['tag' => $tag]) }}" class="bg-slate-700 hover:bg-indigo-600 text-slate-300 hover:text-white text-xs px-2.5 py-1 rounded-md transition-colors">
                        #{{ $tag }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Botones de Acción (Editar, Eliminar y Clonar) -->
        @auth
            <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t border-slate-700/60 items-center">
                
                {{-- Botón Clonar / Usar como Plantilla (para Fichas, Diarios, Campañas y Mapas) --}}
                @if(in_array($resource->type, ['sheet', 'diary', 'campaign', 'map']))
                    @php
                        $cloneRoute = in_array($resource->type, ['sheet', 'diary', 'campaign'])
                            ? route('sheets.create', ['clone_id' => $resource->id, 'type' => $resource->type])
                            : route('maps.create', ['clone_id' => $resource->id]);
                    @endphp
                    <a href="{{ $cloneRoute }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-2 rounded-lg text-sm shadow transition-colors flex items-center gap-1.5">
                        📋 Clonar / Usar como Plantilla
                    </a>
                @endif

                {{-- Botones Editar y Eliminar (Solo Propietario o Recursos Anónimos) --}}
                @if($resource->user_id === null || $resource->user_id === auth()->id())
                    @php
                        $editRoute = route('resources.edit', $resource->id);
                        if(in_array($resource->type, ['sheet', 'diary', 'campaign'])) {
                            $editRoute = route('sheets.create', ['edit_id' => $resource->id]);
                        } elseif($resource->type === 'map') {
                            $editRoute = route('maps.create', ['edit_id' => $resource->id]);
                        }
                    @endphp
                <div class="flex gap-3 mt-6 pt-4 border-t border-slate-700/60">
                    <a href="{{ $editRoute }}" class="bg-amber-600 hover:bg-amber-500 text-white font-bold px-4 py-2 rounded-lg text-sm">
                        ✏️ Editar Recurso
                    </a>

                    <form action="{{ route('resources.destroy', $resource->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este recurso?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-rose-600 hover:bg-rose-500 text-white font-bold px-4 py-2 rounded-lg text-sm">
                            🗑️ Eliminar Recurso
                        </button>
                    </form>
                @endif
            </div>
        @endauth
    </div>

    <!-- VISOR CONTENEDOR DE RECURSO -->
    <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-xl min-h-[400px] flex items-center justify-center">
        @if($resource->type === 'file' && $resource->resourceable)
            @php 
                $file = $resource->resourceable;
                $type = $file->file_type;
                $metadata = $file->metadata ?? [];
                $isPdf = $type === 'pdf' || str_ends_with(strtolower(parse_url($fileUrl ?? '', PHP_URL_PATH) ?? ''), '.pdf');
            
                // Detección de Google Docs / Google Sheets / Google Drive
                $isGoogleDoc = false;
                $googleEmbedUrl = $fileUrl;
                if ($file->is_external && (str_contains($fileUrl, 'docs.google.com') || str_contains($fileUrl, 'drive.google.com'))) {
                    $isGoogleDoc = true;
                    // Adaptamos la URL para incrustación limpia
                    if (str_contains($fileUrl, '/edit')) {
                        $googleEmbedUrl = preg_replace('/\/edit.*$/', '/preview', $fileUrl);
                    }
                }
                $path = parse_url($fileUrl ?? '', PHP_URL_PATH) ?? '';
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                $isOfficeDocument = in_array($extension, [
                    'doc', 'docx', '.docm', 'dot', 'dotx', 'dotm', 'xls', 'xlsx', 'xlsb', 'xlsm', '.xlt', 'xltx', 'xltm', 'ppt', 'pptx', 'pptm', 'pps', 'ppsx', 'ppsm', 'pot', 'potx', 'potm', 'odt', 'ods', 'odp', 'rtf'
                ], true);

                $isHtmlDocument = in_array($extension, [
                    'html', 'htm',
                ], true);
                $isTxtDocument = in_array($extension, [
                    'txt', 'md', 'markdown', 'csv', 'log', 'json', 'xml', 'yml', 'yaml'
                ], true);
            @endphp

            @if($type === 'image')
                <div class="text-center space-y-4">
                    <img src="{{ $fileUrl }}" alt="{{ $resource->title }}" class="max-h-[600px] mx-auto rounded-lg shadow-lg border border-slate-700">
                </div>

            <!-- VISOR PDF CON CARGA NATIVA POR DEFECTO Y ALTERNANCIA -->
            @elseif($isPdf)
                @php
                    $streamUrl = $resource->resourceable && $resource->resourceable->is_external 
                        ? $fileUrl 
                        : route('resources.stream', $resource->id);
                        
                    $urlWithThumbnails = $streamUrl . '#sidebar=thumbs&view=FitH';
                @endphp
                <div class="w-full space-y-4">
                    
                    <!-- Botón para alternar visores -->
                    <div class="flex justify-end">
                        <button id="pdf-toggle-viewer" type="button" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-2 rounded-lg text-xs transition-colors shadow flex items-center gap-2">
                            🎲 Cambiar a Visor Integrado
                        </button>
                    </div>

                    <!-- SUB-VISOR 1: PDF.JS (Custom integrado) -->
                    <div id="viewer-pdfjs" class="hidden space-y-4">
                        <div class="flex flex-wrap justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs gap-2">
                            <div class="flex items-center gap-2">
                                <button id="pdf-prev" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">
                                    ◀ Anterior
                                </button>
                                <span class="text-slate-300">
                                    Página <strong id="pdf-page-num" class="text-slate-200">1</strong> de <strong id="pdf-page-count" class="text-slate-200">--</strong>
                                </span>
                                <button id="pdf-next" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">
                                    Siguiente ▶
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <button id="pdf-zoom-out" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">
                                    🔍- Reducir
                                </button>
                                <span id="pdf-zoom-level" class="text-slate-300 font-semibold">100%</span>
                                <button id="pdf-zoom-in" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">
                                    🔍+ Aumentar
                                </button>
                            </div>
                        </div>

                        <div id="pdf-container" class="overflow-x-auto flex justify-center bg-slate-950 p-4 rounded-lg border border-slate-700 max-h-[750px] overflow-y-auto">
                            <canvas id="pdf-canvas" class="shadow-2xl rounded border border-slate-800 max-w-full h-auto block mx-auto object-contain"></canvas>
                        </div>
                    </div>

                    <!-- SUB-VISOR 2: VISOR NATIVO -->
                    <div id="viewer-native" class="w-full h-[650px]">
                        <iframe src="{{ $urlWithThumbnails }}" class="w-full h-full rounded-lg border border-slate-700"></iframe>
                    </div>

                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const url = JSON.parse('{!! addslashes(json_encode($streamUrl)) !!}');
                        
                        let pdfDoc = null,
                            pageNum = 1,
                            pageRendering = false,
                            pageNumPending = null,
                            scale = 1.2,
                            canvas = document.getElementById('pdf-canvas'),
                            ctx = canvas.getContext('2d'),
                            isScriptLoaded = false;

                        const toggleBtn = document.getElementById('pdf-toggle-viewer');
                        const pdfjsViewer = document.getElementById('viewer-pdfjs');
                        const nativeViewer = document.getElementById('viewer-native');

                        toggleBtn.addEventListener('click', function () {
                            if (pdfjsViewer.classList.contains('hidden')) {
                                pdfjsViewer.classList.remove('hidden');
                                nativeViewer.classList.add('hidden');
                                toggleBtn.innerHTML = '🌐 Cambiar a Visor de Navegador (Nativo)';
                                
                                if (!isScriptLoaded) {
                                    loadPdfJsLibraries();
                                }
                            } else {
                                pdfjsViewer.classList.add('hidden');
                                nativeViewer.classList.remove('hidden');
                                toggleBtn.innerHTML = '🎲 Cambiar a Visor Integrado';
                            }
                        });

                        function loadPdfJsLibraries() {
                            const script = document.createElement('script');
                            script.src = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js";
                            script.async = true;
                            
                            script.onload = function() {
                                window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                                isScriptLoaded = true;
                                initPdfJs();
                            };
                            
                            document.head.appendChild(script);
                        }

                        function initPdfJs() {
                            window.pdfjsLib.getDocument(url).promise.then(function(pdfDoc_Transformed) {
                                pdfDoc = pdfDoc_Transformed;
                                document.getElementById('pdf-page-count').textContent = pdfDoc.numPages;
                                renderPage(pageNum);
                            }).catch(function(error) {
                                console.error("Error al cargar el PDF integrado:", error);
                            });
                        }

                        function renderPage(num) {
                            pageRendering = true;
                            pdfDoc.getPage(num).then(function(page) {
                                const viewport = page.getViewport({ scale: scale });
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;

                                const renderContext = {
                                    canvasContext: ctx,
                                    viewport: viewport
                                };
                                const renderTask = page.render(renderContext);

                                renderTask.promise.then(function() {
                                    pageRendering = false;
                                    if (pageNumPending !== null) {
                                        renderPage(pageNumPending);
                                        pageNumPending = null;
                                    }
                                });
                            });

                            document.getElementById('pdf-page-num').textContent = num;
                            document.getElementById('pdf-zoom-level').textContent = Math.round(scale * 100 / 1.2) + '%';
                        }

                        function queueRenderPage(num) {
                            if (pageRendering) {
                                pageNumPending = num;
                            } else {
                                renderPage(num);
                            }
                        }

                        function onPrevPage() {
                            if (!pdfDoc || pageNum <= 1) return;
                            pageNum--;
                            queueRenderPage(pageNum);
                        }
                        document.getElementById('pdf-prev').addEventListener('click', onPrevPage);

                        function onNextPage() {
                            if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
                            pageNum++;
                            queueRenderPage(pageNum);
                        }
                        document.getElementById('pdf-next').addEventListener('click', onNextPage);

                        document.getElementById('pdf-zoom-in').addEventListener('click', function () {
                            if (!pdfDoc) return;
                            scale += 0.2;
                            queueRenderPage(pageNum);
                        });

                        document.getElementById('pdf-zoom-out').addEventListener('click', function () {
                            if (!pdfDoc || scale <= 0.4) return;
                            scale -= 0.2;
                            queueRenderPage(pageNum);
                        });
                    });
                </script>

            <!-- VISOR 3: DOCUMENTOS (Google Docs / Sheets vs Office Viewer) -->
            @elseif($type === 'document')
                @if($isGoogleDoc)
                    <div class="w-full space-y-4">
                        <div class="flex justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs">
                            <span class="text-slate-300 font-semibold flex items-center gap-2">
                                📊 Documento / Hoja de Cálculo de Google
                            </span>
                            <a href="{{ $fileUrl }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-1.5 rounded-md transition-colors shadow flex items-center gap-1.5">
                                ↗️ Abrir y Editar en Google
                            </a>
                        </div>
                        <iframe src="{{ $googleEmbedUrl }}" class="w-full h-[650px] rounded-lg border border-slate-700 bg-white"></iframe>
                    </div>
                @elseif($isOfficeDocument)
                    <div class="w-full space-y-4">
                        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($fileUrl) }}" class="w-full h-[650px] rounded-lg border border-slate-700"></iframe>
                    </div>
                @elseif($isTxtDocument)
                    {{-- Visor de Archivos de Texto (TXT, MD, CSV, JSON, XML, YAML) --}}
                    <div class="w-full space-y-4">
                        <div class="flex justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs">
                            <span class="text-slate-300 font-semibold flex items-center gap-2">
                                📄 Documento de Texto
                            </span>
                        </div>
                        <iframe src="{{ $fileUrl }}" class="w-full h-[650px] rounded-lg border border-slate-700 bg-white"></iframe>
                    </div>
                @else
                    <iframe
                        src="{{ $fileUrl }}"
                        class="w-full h-[650px] rounded-lg border border-slate-700 bg-white"
                        sandbox="allow-same-origin"
                    ></iframe>
                @endif
            
            @elseif($type === 'audio')
                <div class="w-full max-w-md p-6 bg-slate-900 rounded-xl text-center space-y-4">
                    <p class="text-indigo-400 font-semibold">🎵 Reproductor de Audio</p>

                    <audio
                        id="resource-audio"
                        controls
                        preload="metadata"
                        class="w-full"
                    >
                        <source
                            src="{{ $fileUrl }}"
                            type="{{ $file->mime_type ?: 'audio/ogg' }}"
                        >
                        Tu navegador no puede reproducir este archivo de audio.
                    </audio>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const audio = document.getElementById('resource-audio');

                        if (!audio) {
                            return;
                        }

                        audio.addEventListener('ended', function () {
                            audio.pause();
                            audio.currentTime = 0;
                            audio.load();
                        });

                        audio.addEventListener('play', function () {
                            if (
                                Number.isFinite(audio.duration) &&
                                audio.currentTime >= audio.duration
                            ) {
                                audio.currentTime = 0;
                            }
                        });
                    });
                </script>

            @elseif($type === 'video')
                <video
                    controls
                    preload="metadata"
                    class="w-full max-h-[600px] rounded-lg border border-slate-700"
                >
                    <source
                        src="{{ $fileUrl }}"
                        type="{{ $file->mime_type ?: 'video/mp4' }}"
                    >
                    Tu navegador no puede reproducir este vídeo.
                </video>

            <!-- VISOR 6: ARCHIVOS COMPRIMIDOS (ZIP, TAR.GZ, TGZ, RAR, 7Z) -->
            @elseif($type === 'zip')
                @php
                    $formatLabel = strtoupper($metadata['archive_format'] ?? 'Comprimido');
                    $treeList = !empty($fileTree) ? $fileTree : ($metadata['structure'] ?? []);
                @endphp
                <div class="w-full space-y-4">
                    <div class="flex justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs">
                        <span class="text-indigo-300 font-bold flex items-center gap-2">
                            📦 Estructura del Archivo ({{ $formatLabel }})
                        </span>
                        @if(count($treeList) > 0)
                            <span class="text-slate-300">{{ count($treeList) }} elementos encontrados</span>
                        @endif
                    </div>

                    @if(count($treeList) > 0)
                        <div class="bg-slate-900 rounded-lg p-4 font-mono text-sm max-h-[500px] overflow-y-auto space-y-1 border border-slate-700 select-text">
                            @foreach($treeList as $item)
                                @php
                                    $cleanPath = rtrim($item['path'] ?? $item['name'], '/');
                                    $depth = $cleanPath !== '' ? substr_count($cleanPath, '/') : 0;
                                    $paddingClass = match (min($depth, 6)) {
                                        1 => 'pl-5',
                                        2 => 'pl-10',
                                        3 => 'pl-15',
                                        4 => 'pl-20',
                                        5 => 'pl-25',
                                        6 => 'pl-30',
                                        default => 'pl-0',
                                    };
                                @endphp
                                <div class="flex justify-between items-center py-1 border-b border-slate-800/50 hover:bg-slate-800/50 rounded px-2 transition-colors {{ $item['is_dir'] ? 'text-indigo-300 font-bold' : 'text-slate-300' }}
                                     {{ $paddingClass }}">
                                    
                                    <span class="truncate pr-4 flex items-center gap-2">
                                        <span class="text-base leading-none">{{ $item['is_dir'] ? '📁' : '📄' }}</span>
                                        <span class="font-semibold">{{ $item['name'] }}</span>
                                        <span class="text-slate-400 font-normal text-xs hidden md:inline">({{ $cleanPath }})</span>
                                    </span>

                                    <span class="text-slate-400 text-xs shrink-0 font-mono">
                                        @if($item['is_dir'])
                                            <span class="bg-indigo-950 text-indigo-400 text-[10px] px-1.5 py-0.5 rounded border border-indigo-800/40">Carpeta</span>
                                        @else
                                            {{ $item['size'] > 0 ? number_format($item['size'] / 1024, 1) . ' KB' : '0 KB' }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 bg-slate-900 rounded-lg border border-slate-700 text-center space-y-3">
                            <p class="text-slate-300 text-sm">Contenido empaquetado en formato {{ $formatLabel }}.</p>
                            @if($fileUrl)
                                <a href="{{ $fileUrl }}" download target="_blank" class="inline-block bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-2.5 rounded-lg text-sm transition-colors shadow">
                                    ⬇️ Descargar {{ $formatLabel }} Completo
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

        <!-- VISOR 2: MAPA INTERACTIVO -->
        @elseif($resource->type === 'map')
            @php
                $map = $resource->resourceable;
                $mapImageUrl = $map ? $map->map_image_url : null;
                $markers = $map ? ($map->markers ?? []) : [];
            @endphp

            @if($mapImageUrl)
                <div class="w-full space-y-4">
                    <div id="map-viewer" class="w-full h-[600px] bg-slate-900 border border-slate-700 rounded-lg overflow-hidden"></div>
                </div>

                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const mapUrl = JSON.parse('{!! addslashes(json_encode($mapImageUrl)) !!}');
                        const markersList = JSON.parse('{!! addslashes(json_encode($markers)) !!}');

                        const img = new Image();
                        img.src = mapUrl;
                        img.onload = function () {
                            const h = img.height;
                            const w = img.width;

                            const mapInstance = L.map('map-viewer', {
                                crs: L.CRS.Simple,
                                minZoom: -2
                            });

                            const bounds = [[0, 0], [h, w]];
                            L.imageOverlay(mapUrl, bounds).addTo(mapInstance);
                            mapInstance.fitBounds(bounds);

                            markersList.forEach(function(m) {
                                if (m.y !== undefined && m.x !== undefined) {
                                    L.marker([m.y, m.x])
                                        .addTo(mapInstance)
                                        .bindPopup('<b>' + (m.label || '') + '</b><br>' + (m.description || ''));
                                }
                            });
                        };
                    });
                </script>
            @else
                <p class="text-slate-300">No se pudo cargar la imagen del mapa.</p>
            @endif

        <!-- VISOR 3: FICHAS, DIARIOS Y CAMPAÑAS -->
        @elseif(in_array($resource->type, ['sheet', 'diary', 'campaign']))
            <div class="w-full space-y-4 text-slate-200">
                @if($resource->resourceable && isset($resource->resourceable->content['blocks']))
                    @foreach($resource->resourceable->content['blocks'] as $block)
                        @if($block['type'] === 'header')
                            <h2 class="text-2xl font-bold text-indigo-400 mt-4">{!! $block['data']['text'] !!}</h2>
                        @elseif($block['type'] === 'paragraph')
                            <p class="text-slate-300 leading-relaxed">{!! $block['data']['text'] !!}</p>
                        @elseif($block['type'] === 'table')
                            <div class="overflow-x-auto my-4">
                                <table class="w-full text-sm text-left border border-slate-700">
                                    @foreach($block['data']['content'] as $row)
                                        <tr class="border-b border-slate-700/60">
                                            @foreach($row as $cell)
                                                <td class="p-3 border-r border-slate-700/60">{!! $cell !!}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        @endif
                    @endforeach
                @else
                    <p class="text-slate-300 italic">Este recurso no contiene bloques de contenido.</p>
                @endif
            </div>
        @endif

    </div>
</div>
@endsection
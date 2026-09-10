<?php $__env->startSection('title', $resource->title . ' - CorRol'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    
    <!-- Encabezado del Recurso -->
    <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-xl">
        <div class="flex flex-wrap justify-between items-start gap-4">
            <div>
                <span class="inline-block px-3 py-1 bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-xs font-bold rounded-full mb-2 uppercase">
                    <?php echo e($resource->resourceable->file_type ?? $resource->type); ?>

                </span>
                <h1 class="text-3xl font-bold text-slate-100"><?php echo e($resource->title); ?></h1>
                <?php if($resource->description): ?>
                    <p class="text-slate-300 mt-2 whitespace-pre-line"><?php echo preg_replace('/(https?:\/\/[^\s<]+)/i', '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-indigo-400 hover:underline font-medium">$1</a>', e($resource->description)); ?></p>
                <?php endif; ?>
            </div>

            <?php if($fileUrl): ?>
                <a href="<?php echo e($fileUrl); ?>" download target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3 rounded-lg shadow-lg">
                    ⬇️ Descargar Recurso
                </a>
            <?php endif; ?>
        </div>

        <!-- Metadatos Identificativos -->
        <div class="mt-6 pt-4 border-t border-slate-700/60 grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
            <div>
                <span class="block text-slate-400 text-xs">Juego:</span>
                <?php if($resource->game): ?>
                    <a href="<?php echo e(route('resources.index', ['game' => $resource->game])); ?>" class="font-semibold text-indigo-400 hover:underline">
                        🎲 <?php echo e($resource->game); ?>

                    </a>
                <?php else: ?>
                    <span class="font-semibold text-slate-300">General</span>
                <?php endif; ?>
            </div>

            <div>
                <span class="block text-slate-400 text-xs">Campaña:</span>
                <?php if($resource->campaign): ?>
                    <a href="<?php echo e(route('resources.index', ['campaign' => $resource->campaign])); ?>" class="font-semibold text-indigo-400 hover:underline">
                        📜 <?php echo e($resource->campaign); ?>

                    </a>
                <?php else: ?>
                    <span class="font-semibold text-slate-300">No especificada</span>
                <?php endif; ?>
            </div>

            <div>
                <span class="block text-slate-400 text-xs">Autor Original:</span>
                <?php if($resource->author): ?>
                    <a href="<?php echo e(route('resources.index', ['author' => $resource->author])); ?>" class="font-semibold text-indigo-400 hover:underline">
                        ✍️ <?php echo e($resource->author); ?>

                    </a>
                <?php else: ?>
                    <span class="font-semibold text-slate-300">Desconocido</span>
                <?php endif; ?>
            </div>

            <div>
                <span class="block text-slate-400 text-xs">Subido por:</span>
                <?php if($resource->user): ?>
                    <a href="<?php echo e(route('resources.index', ['user_id' => $resource->user_id])); ?>" class="font-semibold text-indigo-400 hover:underline">
                        👤 <?php echo e($resource->user->name); ?>

                    </a>
                <?php else: ?>
                    <span class="font-semibold text-slate-300">🌐 Anónimo</span>
                <?php endif; ?>
            </div>

            <div>
                <span class="block text-slate-400 text-xs">Privacidad:</span>
                <span class="font-semibold <?php echo e($resource->privacy === 'private' ? 'text-amber-400' : 'text-emerald-400'); ?>">
                    <?php echo e(ucfirst($resource->privacy)); ?>

                </span>
            </div>
        </div>

        <?php if(!empty($resource->tags)): ?>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php $__currentLoopData = $resource->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('resources.index', ['tag' => $tag])); ?>" class="bg-slate-700 hover:bg-indigo-600 text-slate-300 hover:text-white text-xs px-2.5 py-1 rounded-md transition-colors">
                        #<?php echo e($tag); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <!-- Botones de Acción (Editar, Eliminar, Favorito y Clonar) -->
        <?php if(auth()->guard()->check()): ?>
            <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t border-slate-700/60 items-center">
                
                
                <form action="<?php echo e(route('resources.favorite.toggle', $resource->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="font-bold px-4 py-2 rounded-lg text-sm shadow transition-colors flex items-center gap-1.5 <?php echo e($isFavorited ? 'bg-amber-600 hover:bg-amber-500 text-white' : 'bg-slate-700 hover:bg-slate-600 text-slate-200'); ?>">
                        <?php echo e($isFavorited ? '⭐ Quitar de Favoritos' : '☆ Añadir a Favoritos'); ?>

                    </button>
                </form>

                
                <?php if(in_array($resource->type, ['sheet', 'diary', 'campaign', 'map'])): ?>
                    <?php
                        $cloneRoute = in_array($resource->type, ['sheet', 'diary', 'campaign'])
                            ? route('sheets.create', ['clone_id' => $resource->id, 'type' => $resource->type])
                            : route('maps.create', ['clone_id' => $resource->id]);
                    ?>
                    <a href="<?php echo e($cloneRoute); ?>" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-2 rounded-lg text-sm shadow transition-colors flex items-center gap-1.5">
                        📋 Clonar / Usar como Plantilla
                    </a>
                <?php endif; ?>

                
                <?php if($resource->user_id === null || $resource->user_id === auth()->id()): ?>
                    <?php
                        $editRoute = route('resources.edit', $resource->id);
                        if(in_array($resource->type, ['sheet', 'diary', 'campaign'])) {
                            $editRoute = route('sheets.create', ['edit_id' => $resource->id]);
                        } elseif($resource->type === 'map') {
                            $editRoute = route('maps.create', ['edit_id' => $resource->id]);
                        }
                    ?>
                    
                    <a href="<?php echo e($editRoute); ?>" class="bg-amber-600 hover:bg-amber-500 text-white font-bold px-4 py-2 rounded-lg text-sm">
                        ✏️ Editar Recurso
                    </a>

                    <form action="<?php echo e(route('resources.destroy', $resource->id)); ?>" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este recurso?');" class="inline">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="bg-rose-600 hover:bg-rose-500 text-white font-bold px-4 py-2 rounded-lg text-sm">
                            🗑️ Eliminar Recurso
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- VISOR CONTENEDOR DE RECURSO -->
    <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-xl min-h-[400px] flex items-center justify-center">
        <?php if($resource->type === 'file' && $resource->resourceable): ?>
            <?php 
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
            ?>

            <?php if($type === 'image'): ?>
                <div class="text-center space-y-4">
                    <img src="<?php echo e($fileUrl); ?>" alt="<?php echo e($resource->title); ?>" class="max-h-[600px] mx-auto rounded-lg shadow-lg border border-slate-700">
                </div>

            
            <!-- VISOR PDF / CÓMIC -->
            <?php elseif($isPdf): ?>
                <?php
                    $streamUrl = $resource->resourceable && $resource->resourceable->is_external 
                        ? route('resources.proxy-stream', $resource->id) 
                        : route('resources.stream', $resource->id);
                        
                    $urlWithThumbnails = $streamUrl . '#sidebar=thumbs&view=FitH';
                ?>
                
                <?php if($resource->isComic()): ?>

                    <!-- ========================================== -->
                    <!-- VISOR DE CÓMIC INTERACTIVO -->
                    <!-- ========================================== -->
                    <div class="w-full space-y-4 relative" id="comic-reader-wrapper">
                        <!-- Menú Superior Completo -->
                        <div id="comic-menu" class="flex flex-wrap justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs gap-3">
                            <span class="text-indigo-400 font-bold uppercase tracking-wider flex items-center gap-2">
                                🦸 Modo Lectura: Cómic Interactivo
                            </span>
                            
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Navegación -->
                                <button id="btn-comic-prev" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded transition shadow font-medium">◀ Anterior</button>
                                
                                <!-- Selectores desplegables -->
                                <select id="select-comic-page" class="bg-slate-800 text-slate-200 border border-slate-700 rounded px-2 py-1.5 focus:outline-none focus:border-indigo-500">
                                    <option value="">Página...</option>
                                </select>
                                <select id="select-comic-panel" class="bg-slate-800 text-slate-200 border border-slate-700 rounded px-2 py-1.5 focus:outline-none focus:border-indigo-500">
                                    <option value="">Viñeta...</option>
                                </select>

                                <span class="text-slate-300 font-mono px-1"><span id="current-panel-indicator">1 / 1</span></span>
                                <button id="btn-comic-next" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded transition shadow font-medium">Siguiente ▶</button>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Zoom Manual -->
                                <button id="btn-comic-zoom-out" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-2.5 py-1.5 rounded transition shadow" title="Reducir Zoom">🔍-</button>
                                <!-- Cambiar <select id="comic-zoom-select"> por: -->
                                <div class="flex items-center gap-1 bg-slate-800 border border-slate-700 rounded px-1 py-0.5">
                                    <input type="number" id="comic-zoom-input" min="10" max="500" step="5" value="100" class="w-14 bg-transparent text-slate-200 font-mono text-xs text-center focus:outline-none">
                                    <span class="text-slate-400 font-mono text-xs pr-1">%</span>
                                </div>
                                <button id="btn-comic-zoom-in" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-2.5 py-1.5 rounded transition shadow" title="Aumentar Zoom">🔍+</button>
                                <button id="btn-comic-zoom-reset" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-2.5 py-1.5 rounded transition shadow font-medium" title="Restablecer a 100%">100%</button>

                                <!-- Ver Viñeta vs Ver Página -->
                                <button id="btn-comic-mode" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded transition shadow font-medium">Ver Página</button>

                                <!-- Pantalla Completa -->
                                <button id="btn-comic-fullscreen" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded transition shadow font-medium">⛶ Pantalla Completa</button>

                                <!-- Vista PDF / Estándar (Alterna al visor normal) -->
                                <button id="btn-comic-to-standard-pdf" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded transition shadow font-medium">Vista PDF</button>
                            </div>
                        </div>

                        <!-- Lienzo del visor (Con soporte para arrastrar / panning) -->
                        <!-- FIX: Eliminados 'flex items-center justify-center' del contenedor -->
                        <div id="comic-viewport-container" class="w-full h-[700px] bg-black rounded-lg border border-slate-700 overflow-hidden relative shadow-2xl cursor-grab">
                            
                            <!-- FIX: Añadido 'absolute top-0 left-0' al hijo -->
                            <div id="comic-viewport" class="absolute top-0 left-0 transition-transform duration-75 ease-out origin-top-left" style="transform: scale(1) translate(0px, 0px);">
                                <canvas id="comic-canvas" class="block"></canvas>
                            </div>
                            
                            <!-- Loading overlay -->
                            <div id="comic-loader" class="absolute inset-0 bg-slate-900/80 flex items-center justify-center text-white font-bold text-xl z-10 hidden">
                                ⏳ Cargando página...
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- VISOR ESTÁNDAR DE PDF (PDF.JS + NATIVO) -->
                    <!-- ========================================== -->
                    <div id="standard-pdf-container-wrapper" class="hidden w-full space-y-4">
                        <div class="flex justify-between items-center">
                            <button id="btn-back-to-comic" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 font-semibold px-4 py-2 rounded-lg text-xs transition-colors shadow">
                                ◀ Volver al Modo Cómic Interactivo
                            </button>
                            <button id="pdf-toggle-viewer" type="button" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-2 rounded-lg text-xs transition-colors shadow flex items-center gap-2">
                                🎲 Cambiar a Visor Integrado
                            </button>
                        </div>

                        <!-- SUB-VISOR 1: PDF.JS (Custom integrado con Panning idéntico) -->
                        <div id="viewer-pdfjs" class="hidden space-y-4">
                            <div class="flex flex-wrap justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs gap-2">
                                <div class="flex items-center gap-2">
                                    <button id="pdf-prev" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">◀ Anterior</button>
                                    <span class="text-slate-300">Página <strong id="pdf-page-num" class="text-slate-200">1</strong> de <strong id="pdf-page-count" class="text-slate-200">--</strong></span>
                                    <button id="pdf-next" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">Siguiente ▶</button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button id="pdf-zoom-out" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">🔍- Reducir</button>
                                    <!-- Cambiar <select id="pdfc-zoom-select"> por: -->
                                    <div class="flex items-center gap-1 bg-slate-800 border border-slate-700 rounded px-1 py-0.5">
                                        <input type="number" id="pdfc-zoom-input" min="10" max="500" step="5" value="100" class="w-14 bg-transparent text-slate-200 font-mono text-xs text-center focus:outline-none">
                                        <span class="text-slate-400 font-mono text-xs pr-1">%</span>
                                    </div>
                                    <button id="pdf-zoom-in" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">🔍+ Aumentar</button>
                                    <button id="pdfc-zoom-reset" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-2.5 py-1.5 rounded transition shadow font-medium" title="Restablecer a 100%">100%</button>
                                </div>
                            </div>
                            <!-- Contenedor con Panning (Arrastrar) y centrado -->
                            <div id="pdf-viewport-container" class="w-full h-[680px] bg-slate-950 rounded-lg border border-slate-700 overflow-hidden relative shadow-2xl cursor-grab">
                                <div id="pdf-viewport" class="absolute top-0 left-0 transition-transform duration-75 ease-out origin-top-left" style="transform: scale(1) translate(0px, 0px);">
                                    <canvas id="pdf-canvas" class="shadow-2xl rounded border border-slate-800 block"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- SUB-VISOR 2: VISOR NATIVO -->
                        <div id="viewer-native" class="w-full h-[650px]">
                            <?php
                                $streamUrl = $resource->resourceable && $resource->resourceable->is_external 
                                    ? route('resources.proxy-stream', $resource->id) 
                                    : route('resources.stream', $resource->id);
                                $urlWithThumbnails = $streamUrl . '#sidebar=thumbs&view=FitH';
                            ?>
                            <iframe src="<?php echo e($urlWithThumbnails); ?>" class="w-full h-full rounded-lg border border-slate-700"></iframe>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const url = JSON.parse('<?php echo addslashes(json_encode($streamUrl)); ?>');
                            const comicMetadata = <?php echo json_encode($resource->comic_metadata ?? []); ?>;
                            
                            let timeline = [];
                            let pagesMap = {};
                            let pdfDoc = null;
                            let currentPageLoaded = null;
                            let viewMode = 'panel'; // 'panel' o 'page'
                            let comicZoomMultiplier = 1.0;
                            let currentIdx = 0;
                            let initialPinchDistance = null;
                            let startZoomMultiplier = 1.0;

                            // Variables para Arrastrar / Panning
                            let isDragging = false;
                            let startX = 0, startY = 0;
                            let panX = 0, panY = 0;
                            let currentScale = 1;
                            let currentTranslateX = 0;
                            let currentTranslateY = 0;

                            // Variables de Arrastre y Zoom para Cómic
                            let comicIsDragging = false, comicStartX = 0, comicStartY = 0, comicPanX = 0, comicPanY = 0;
                            let comicCurrentScale = 1, comicTranslateX = 0, comicTranslateY = 0;
                            
                            const canvas = document.getElementById('comic-canvas');
                            const ctx = canvas.getContext('2d');
                            const viewportDiv = document.getElementById('comic-viewport');
                            const container = document.getElementById('comic-viewport-container');
                            const loader = document.getElementById('comic-loader');
                            
                            const selectPage = document.getElementById('select-comic-page');
                            const selectPanel = document.getElementById('select-comic-panel');

                            // Carga de PDF.js local

                            const script = document.createElement('script');
                            // Usamos la ruta local de tu servidor
                            script.src = "<?php echo e(asset('js/vendor/pdf.min.js')); ?>";
                            script.onload = function() {
                                // Configuramos el worker apuntando al archivo local descargado
                                window.pdfjsLib.GlobalWorkerOptions.workerSrc = "<?php echo e(asset('js/vendor/pdf.worker.min.js')); ?>";
                                
                                // Mostrar el loader inmediatamente al iniciar la carga del PDF
                                loader.classList.remove('hidden');
                                loader.innerHTML = '⏳ Descargando y preparando PDF completo...';

                                window.pdfjsLib.getDocument(url).promise.then(pdf => {
                                    pdfDoc = pdf;

                                    // Construir línea de tiempo incluyendo páginas sin viñetas (formato completo)
                                    for (let pNum = 1; pNum <= pdfDoc.numPages; pNum++) {
                                        if (!pagesMap[pNum]) pagesMap[pNum] = [];
                                        
                                        if (comicMetadata[pNum] && Array.isArray(comicMetadata[pNum]) && comicMetadata[pNum].length > 0) {
                                            comicMetadata[pNum].forEach((panel, panelIdx) => {
                                                timeline.push({ page: pNum, panelIndex: panelIdx, ...panel });
                                                pagesMap[pNum].push(timeline[timeline.length - 1]);
                                            });
                                        } else {
                                            // Página completa por defecto si no tiene viñetas configuradas
                                            timeline.push({ page: pNum, panelIndex: 0, type: 'rect', x: 0, y: 0, w: 1, h: 1 });
                                            pagesMap[pNum].push(timeline[timeline.length - 1]);
                                        }
                                    }

                                    // Poblar selector de páginas
                                    Object.keys(pagesMap).forEach(pNum => {
                                        let opt = document.createElement('option');
                                        opt.value = pNum;
                                        opt.textContent = 'Página ' + pNum;
                                        selectPage.appendChild(opt);
                                    });

                                    showPanel(0);
                                });
                            };
                            document.head.appendChild(script);

                            function updateComicTransform() {
                                if (!viewportDiv) return;
                                viewportDiv.style.transform = `scale(${comicCurrentScale}) translate(${comicTranslateX + comicPanX}px, ${comicTranslateY + comicPanY}px)`;
                            }

                            function showPanel(idx) {
                                if(idx < 0 || idx >= timeline.length) return;
                                currentIdx = idx;
                                const item = timeline[idx];
                                
                                document.getElementById('current-panel-indicator').textContent = (idx + 1) + ' / ' + timeline.length;
                                selectPage.value = item.page;
                                selectPanel.innerHTML = '<option value="">Viñeta...</option>';
                                pagesMap[item.page]?.forEach((p, pIdx) => {
                                    let opt = document.createElement('option');
                                    opt.value = timeline.indexOf(p);
                                    opt.textContent = 'Viñeta ' + (pIdx + 1);
                                    selectPanel.appendChild(opt);
                                });
                                selectPanel.value = idx;


                                // Al cambiar de viñeta se resetea el paneo manual
                                comicPanX = 0;
                                comicPanY = 0;

                                if(currentPageLoaded === item.page) {
                                    renderComicCamera(item);
                                } else {
                                    loader.classList.remove('hidden');
                                    pdfDoc.getPage(item.page).then(page => {
                                        const viewport = page.getViewport({ scale: 2.0 });
                                        canvas.width = viewport.width;
                                        canvas.height = viewport.height;
                                        
                                        page.render({ canvasContext: ctx, viewport: viewport }).promise.then(() => {
                                            currentPageLoaded = item.page;
                                            loader.classList.add('hidden');
                                            renderComicCamera(item);
                                        });
                                    });
                                }
                            }

                            function renderComicCamera(panel) {
                                if (!container) return;
                                const cW = container.clientWidth;
                                const cH = container.clientHeight;
                                let pX = 0, pY = 0, pW = canvas.width, pH = canvas.height;

                                // Reseteamos el recorte previo
                                canvas.style.clipPath = 'none';

                                if (viewMode === 'page') {
                                    pX = 0;
                                    pY = 0;
                                    pW = canvas.width;
                                    pH = canvas.height;
                                } else if (panel.type === 'polygon' && Array.isArray(panel.points) && panel.points.length > 0) {
                                    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                                    let clipPoints = [];
                                    
                                    panel.points.forEach(pt => {
                                        if (pt.x < minX) minX = pt.x;
                                        if (pt.y < minY) minY = pt.y;
                                        if (pt.x > maxX) maxX = pt.x;
                                        if (pt.y > maxY) maxY = pt.y;
                                        
                                        clipPoints.push(`${(pt.x * 100).toFixed(2)}% ${(pt.y * 100).toFixed(2)}%`);
                                    });
                                    
                                    pX = minX * canvas.width;
                                    pY = minY * canvas.height;
                                    pW = (maxX - minX) * canvas.width;
                                    pH = (maxY - minY) * canvas.height;
                                    
                                    canvas.style.clipPath = `polygon(${clipPoints.join(', ')})`;
                                } else {
                                    // Formato Rectangular con recorte recortado activo
                                    const rawX = panel.x ?? panel.left ?? 0;
                                    const rawY = panel.y ?? panel.top ?? 0;
                                    const rawW = panel.w ?? panel.width ?? 1;
                                    const rawH = panel.h ?? panel.height ?? 1;

                                    if (rawX === 0 && rawY === 0 && rawW === 1 && rawH === 1) {
                                        pX = 0; pY = 0; pW = canvas.width; pH = canvas.height;
                                    } else {
                                        pX = rawX * canvas.width;
                                        pY = rawY * canvas.height;
                                        pW = rawW * canvas.width;
                                        pH = rawH * canvas.height;

                                        const xP = (rawX * 100).toFixed(2);
                                        const yP = (rawY * 100).toFixed(2);
                                        const rightP = ((rawX + rawW) * 100).toFixed(2);
                                        const bottomP = ((rawY + rawH) * 100).toFixed(2);

                                        canvas.style.clipPath = `polygon(${xP}% ${yP}%, ${rightP}% ${yP}%, ${rightP}% ${bottomP}%, ${xP}% ${bottomP}%)`;
                                    }
                                }

                                if (pW <= 0) pW = canvas.width;
                                if (pH <= 0) pH = canvas.height;

                                const scaleX = cW / pW;
                                const scaleY = cH / pH;
                                
                                comicCurrentScale = Math.min(scaleX, scaleY) * comicZoomMultiplier;

                                comicTranslateX = (cW / 2) / comicCurrentScale - (pX + pW / 2);
                                comicTranslateY = (cH / 2) / comicCurrentScale - (pY + pH / 2);

                                updateComicTransform();
                                updateZoomInput(); 
                            }

                            function updateTransform() {
                                viewportDiv.style.transform = `scale(${currentScale}) translate(${currentTranslateX + panX}px, ${currentTranslateY + panY}px)`;
                            }

                            // Eventos de Paneo (Arrastrar con el ratón)
                            container.addEventListener('mousedown', (e) => {
                                comicIsDragging = true;
                                comicStartX = e.clientX;
                                comicStartY = e.clientY;
                                container.style.cursor = 'grabbing';
                            });

                            window.addEventListener('mousemove', (e) => {
                                if (!comicIsDragging) return;
                                comicPanX += (e.clientX - comicStartX) / comicCurrentScale;
                                comicPanY += (e.clientY - comicStartY) / comicCurrentScale;
                                comicStartX = e.clientX;
                                comicStartY = e.clientY;
                                updateComicTransform();
                            });

                            window.addEventListener('mouseup', () => {
                                if (comicIsDragging) {
                                    comicIsDragging = false;
                                    container.style.cursor = 'grab';
                                }
                            });

                            // Eventos de Navegación
                            document.getElementById('btn-comic-prev').addEventListener('click', () => showPanel(currentIdx - 1));
                            document.getElementById('btn-comic-next').addEventListener('click', () => showPanel(currentIdx + 1));

                            selectPage.addEventListener('change', (e) => {
                                let pNum = parseInt(e.target.value);
                                if (pagesMap[pNum]?.[0]) showPanel(timeline.indexOf(pagesMap[pNum][0]));
                            });

                            selectPanel.addEventListener('change', (e) => {
                                let idx = parseInt(e.target.value);
                                if (!isNaN(idx)) showPanel(idx);
                            });

                            // Zoom manual
                            document.getElementById('btn-comic-zoom-in').addEventListener('click', () => { 
                                comicZoomMultiplier = Math.min(comicZoomMultiplier + 0.2, 3.0); 
                                showPanel(currentIdx); 
                            });
                            document.getElementById('btn-comic-zoom-out').addEventListener('click', () => { 
                                comicZoomMultiplier = Math.max(comicZoomMultiplier - 0.2, 0.5); 
                                showPanel(currentIdx); 
                            });

                            // Modo Ver Viñeta / Ver Página
                            const modeBtn = document.getElementById('btn-comic-mode');
                            modeBtn.addEventListener('click', () => {
                                viewMode = viewMode === 'panel' ? 'page' : 'panel';
                                modeBtn.textContent = viewMode === 'panel' ? 'Ver Página' : 'Ver Viñeta';
                                showPanel(currentIdx);
                            });

                            // Alternar vistas Cómic <-> PDF Estándar
                            const comicWrapper = document.getElementById('comic-reader-wrapper');
                            const standardPdfWrapper = document.getElementById('standard-pdf-container-wrapper');
                            
                            document.getElementById('btn-comic-to-standard-pdf').addEventListener('click', () => {
                                comicWrapper.classList.add('hidden');
                                standardPdfWrapper.classList.remove('hidden');
                                
                                // SOLUCIÓN 1: Activar por defecto el visor NATIVO al cambiar para evitar la página en negro
                                document.getElementById('viewer-native').classList.remove('hidden');
                                document.getElementById('viewer-pdfjs').classList.add('hidden');
                                
                                // Nos aseguramos de que el texto del botón refleje que se puede pasar al integrado
                                document.getElementById('pdf-toggle-viewer').innerHTML = '🎲 Cambiar a Visor Integrado';
                            });

                            document.getElementById('btn-back-to-comic').addEventListener('click', () => {
                                standardPdfWrapper.classList.add('hidden');
                                comicWrapper.classList.remove('hidden');
                                setTimeout(() => showPanel(currentIdx), 50);
                            });

                            // ==========================================
                            // LÓGICA DEL VISOR PDF.JS ESTÁNDAR (Con Panning y 100% Ajustado)
                            // ==========================================
                            let pdfjsInitialized = false;
                            let pdfPageNum = 1;
                            let pdfZoomMultiplier = 1.0;
                            
                            let pdfIsDragging = false, pdfStartX = 0, pdfStartY = 0, pdfPanX = 0, pdfPanY = 0;
                            let pdfCurrentScale = 1, pdfTranslateX = 0, pdfTranslateY = 0;

                            const toggleBtn = document.getElementById('pdf-toggle-viewer');
                            const pdfjsViewer = document.getElementById('viewer-pdfjs');
                            const nativeViewer = document.getElementById('viewer-native');
                            const pdfCanvas = document.getElementById('pdf-canvas');
                            const pdfCtx = pdfCanvas.getContext('2d');
                            const pdfContainerContainer = document.getElementById('pdf-viewport-container');
                            const pdfViewportDiv = document.getElementById('pdf-viewport');

                            toggleBtn?.addEventListener('click', function () {
                                if (pdfjsViewer.classList.contains('hidden')) {
                                    pdfjsViewer.classList.remove('hidden');
                                    nativeViewer.classList.add('hidden');
                                    toggleBtn.innerHTML = '🌐 Cambiar a Visor de Navegador (Nativo)';
                                    if (!pdfjsInitialized && pdfDoc) {
                                        pdfjsInitialized = true;
                                        document.getElementById('pdf-page-count').textContent = pdfDoc.numPages;
                                        renderStandardPdfPage(pdfPageNum, pdfDoc);
                                    }
                                } else {
                                    pdfjsViewer.classList.add('hidden');
                                    nativeViewer.classList.remove('hidden');
                                    toggleBtn.innerHTML = '🎲 Cambiar a Visor Integrado';
                                }
                            });

                            function updatePdfTransform() {
                                if (!pdfViewportDiv) return;
                                pdfViewportDiv.style.transform = `scale(${pdfCurrentScale}) translate(${pdfTranslateX + pdfPanX}px, ${pdfTranslateY + pdfPanY}px)`;
                            }


                            function renderStandardPdfPage(num) {
                                if (!pdfDoc) return;
                                pdfDoc.getPage(num).then(function(page) {
                                    const viewport = page.getViewport({ scale: 2.0 }); // Renderizado nítido en alta resolución
                                    pdfCanvas.height = viewport.height;
                                    pdfCanvas.width = viewport.width;

                                    page.render({ canvasContext: pdfCtx, viewport: viewport }).promise.then(() => {
                                        const cW = pdfContainerContainer.clientWidth;
                                        const cH = pdfContainerContainer.clientHeight;
                                        
                                        // Calcula la escala para que al 100% (zoomMultiplier = 1.0) encaje perfectamente entera en el visor
                                        const scaleX = cW / pdfCanvas.width;
                                        const scaleY = cH / pdfCanvas.height;
                                        const fitScale = Math.min(scaleX, scaleY);

                                        pdfCurrentScale = fitScale * pdfZoomMultiplier;
                                        pdfPanX = 0;
                                        pdfPanY = 0;

                                        pdfTranslateX = (cW / 2) / pdfCurrentScale - (pdfCanvas.width / 2);
                                        pdfTranslateY = (cH / 2) / pdfCurrentScale - (pdfCanvas.height / 2);

                                        updatePdfTransform();
                                    });
                                });

                                document.getElementById('pdf-page-num').textContent = num;
                                const zoomLevelElem = document.getElementById('pdfc-zoom-level');
                                if (zoomLevelElem) {
                                    zoomLevelElem.textContent = Math.round(pdfZoomMultiplier * 100) + '%';
                                }
                                const zoomInputElem = document.getElementById('pdfc-zoom-input');
                                if (zoomInputElem) {
                                    zoomInputElem.value = Math.round(pdfZoomMultiplier * 100);
                                }
                            }

                            // Eventos de Panning PDF Estándar
                            if (pdfContainerContainer) {
                                pdfContainerContainer.addEventListener('mousedown', (e) => {
                                    pdfIsDragging = true;
                                    pdfStartX = e.clientX;
                                    pdfStartY = e.clientY;
                                    pdfContainerContainer.style.cursor = 'grabbing';
                                });

                                window.addEventListener('mousemove', (e) => {
                                    if (!pdfIsDragging) return;
                                    pdfPanX += (e.clientX - pdfStartX) / pdfCurrentScale;
                                    pdfPanY += (e.clientY - pdfStartY) / pdfCurrentScale;
                                    pdfStartX = e.clientX;
                                    pdfStartY = e.clientY;
                                    updatePdfTransform();
                                });

                                window.addEventListener('mouseup', () => {
                                    if (pdfIsDragging) {
                                        pdfIsDragging = false;
                                        pdfContainerContainer.style.cursor = 'grab';
                                    }
                                });
                            }

                            // Controles de Paginación y Zoom PDF Estándar
                            document.getElementById('pdf-zoom-in')?.addEventListener('click', () => {
                                if (!pdfDoc) return;
                                pdfZoomMultiplier = Math.min(pdfZoomMultiplier + 0.2, 3.0);
                                renderStandardPdfPage(pdfPageNum);
                            });

                            document.getElementById('pdf-zoom-out')?.addEventListener('click', () => {
                                if (!pdfDoc || pdfZoomMultiplier <= 0.4) return;
                                pdfZoomMultiplier = Math.max(pdfZoomMultiplier - 0.2, 0.4);
                                renderStandardPdfPage(pdfPageNum);
                            });

                            document.getElementById('pdf-prev')?.addEventListener('click', () => {
                                if (!pdfDoc || pdfPageNum <= 1) return;
                                pdfPageNum--;
                                renderStandardPdfPage(pdfPageNum);
                            });

                            document.getElementById('pdf-next')?.addEventListener('click', () => {
                                if (!pdfDoc || pdfPageNum >= pdfDoc.numPages) return;
                                pdfPageNum++;
                                renderStandardPdfPage(pdfPageNum);
                            });

                            // Pantalla Completa
                            const fullscreenBtn = document.getElementById('btn-comic-fullscreen');
                            fullscreenBtn.addEventListener('click', () => {
                                if (!document.fullscreenElement) {
                                    comicWrapper.requestFullscreen().catch(err => console.error(err));
                                } else {
                                    document.exitFullscreen();
                                }
                            });

                            document.addEventListener('fullscreenchange', () => {
                                const btnPdf = document.getElementById('btn-comic-to-standard-pdf');
                                const viewportContainer = document.getElementById('comic-viewport-container');

                                if (document.fullscreenElement) {
                                    fullscreenBtn.textContent = '⛶ Salir';
                                    
                                    // 1. Ocultar el botón de Vista PDF
                                    btnPdf.classList.add('hidden');

                                    // 2. Usar Flexbox para ajustar la altura perfectamente en móviles y escritorio
                                    comicWrapper.classList.remove('space-y-4', 'overflow-auto');
                                    comicWrapper.classList.add('fixed', 'inset-0', 'z-50', 'bg-black', 'p-2', 'sm:p-4', 'flex', 'flex-col', 'overflow-hidden');
                                    
                                    // El contenedor del lienzo toma todo el espacio sobrante automáticamente
                                    viewportContainer.style.height = '0'; // Reseteamos la altura fija
                                    viewportContainer.classList.add('flex-1', 'mt-3');
                                    
                                } else {
                                    fullscreenBtn.textContent = '⛶ Pantalla Completa';
                                    
                                    // 1. Mostrar de nuevo el botón de Vista PDF
                                    btnPdf.classList.remove('hidden');

                                    // 2. Restaurar las clases y alturas originales
                                    comicWrapper.classList.remove('fixed', 'inset-0', 'z-50', 'bg-black', 'p-2', 'sm:p-4', 'flex', 'flex-col', 'overflow-hidden');
                                    comicWrapper.classList.add('space-y-4'); // Mantiene el margen original
                                    
                                    viewportContainer.classList.remove('flex-1', 'mt-3');
                                    viewportContainer.style.height = '700px';
                                }
                                
                                // Forzar renderizado para ajustar el zoom a las nuevas dimensiones
                                setTimeout(() => showPanel(currentIdx), 50);
                            });

                            // Recalcular dimensiones y viñeta activa al rotar el dispositivo o redimensionar la ventana
                            window.addEventListener('resize', () => {
                                if (pdfDoc) {
                                    setTimeout(() => showPanel(currentIdx), 150);
                                }
                            });

                            window.addEventListener('orientationchange', () => {
                                if (pdfDoc) {
                                    setTimeout(() => showPanel(currentIdx), 300);
                                }
                            });

                            // Atajos de teclado
                            document.addEventListener('keydown', (e) => {
                                if(document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'SELECT') return;
                                if(e.key === 'ArrowRight' || e.key === 'ArrowDown') showPanel(currentIdx + 1);
                                if(e.key === 'ArrowLeft' || e.key === 'ArrowUp') showPanel(currentIdx - 1);
                            });

                            // Eventos táctiles para Arrastrar (1 dedo) y Pinch-to-Zoom (2 dedos)
                            container.addEventListener('touchstart', (e) => {
                                if (e.touches.length === 1) {
                                    comicIsDragging = true;
                                    comicStartX = e.touches[0].clientX;
                                    comicStartY = e.touches[0].clientY;
                                } else if (e.touches.length === 2) {
                                    comicIsDragging = false; // Desactivar arrastre si se usan dos dedos
                                    initialPinchDistance = Math.hypot(
                                        e.touches[0].clientX - e.touches[1].clientX,
                                        e.touches[0].clientY - e.touches[1].clientY
                                    );
                                    startZoomMultiplier = comicZoomMultiplier;
                                }
                            }, { passive: true });

                            container.addEventListener('touchmove', (e) => {
                                if (e.touches.length === 1 && comicIsDragging) {
                                    e.preventDefault(); // Evita el scroll de la página al mover la imagen
                                    comicPanX += (e.touches[0].clientX - comicStartX) / comicCurrentScale;
                                    comicPanY += (e.touches[0].clientY - comicStartY) / comicCurrentScale;
                                    comicStartX = e.touches[0].clientX;
                                    comicStartY = e.touches[0].clientY;
                                    updateComicTransform();
                                } else if (e.touches.length === 2 && initialPinchDistance !== null) {
                                    e.preventDefault(); // Evita el zoom nativo del navegador
                                    const currentDistance = Math.hypot(
                                        e.touches[0].clientX - e.touches[1].clientX,
                                        e.touches[0].clientY - e.touches[1].clientY
                                    );
                                    const factor = currentDistance / initialPinchDistance;
                                    comicZoomMultiplier = Math.min(Math.max(startZoomMultiplier * factor, 0.5), 4.0);
                                    showPanel(currentIdx);
                                }
                            }, { passive: false });

                            container.addEventListener('touchend', (e) => {
                                if (e.touches.length === 0) {
                                    comicIsDragging = false;
                                }
                                if (e.touches.length < 2) {
                                    initialPinchDistance = null;
                                }
                            });

                            // Zoom con la rueda del ratón (Ctrl + Scroll)
                            container.addEventListener('wheel', (e) => {
                                if (e.ctrlKey) {
                                    e.preventDefault(); // Evita el zoom de la página completa del navegador
                                    
                                    if (e.deltaY < 0) {
                                        comicZoomMultiplier = Math.min(comicZoomMultiplier + 0.15, 4.0);
                                    } else {
                                        comicZoomMultiplier = Math.max(comicZoomMultiplier - 0.15, 0.5);
                                    }
                                    
                                    showPanel(currentIdx);
                                }
                            }, { passive: false });

                            // Zoom de comic interactivo con input numérico (en lugar de <select>): 
                            const comicZoomInput = document.getElementById('comic-zoom-input');
                            function updateZoomInput() {
                                if (comicZoomInput) {
                                    comicZoomInput.value = Math.round(comicZoomMultiplier * 100);
                                }
                            }
                            // Para el Cómic:
                            comicZoomInput?.addEventListener('change', (e) => {
                                let val = parseFloat(e.target.value);
                                if (isNaN(val) || val <= 0) val = 100;
                                comicZoomMultiplier = val / 100;
                                showPanel(currentIdx);
                            });
                            // Botón de Reset 100% (actualiza también el input):
                            document.getElementById('btn-comic-zoom-reset')?.addEventListener('click', () => {
                                comicZoomMultiplier = 1.0;
                                updateZoomInput();
                                showPanel(currentIdx);
                            });

                            // Zoom de PDF estándar con input numérico (en lugar de <select>):
                            const pdfcZoomInput = document.getElementById('pdfc-zoom-input');
                            // Para el PDF:
                            pdfcZoomInput?.addEventListener('change', (e) => {
                                let val = parseFloat(e.target.value);
                                if (isNaN(val) || val <= 0) val = 100;
                                pdfZoomMultiplier = val / 100;
                                renderStandardPdfPage(pdfPageNum);
                            });
                            // Botón de Reset 100% (actualiza también el input):
                            document.getElementById('pdfc-zoom-reset')?.addEventListener('click', () => {
                                if (!pdfDoc) return;
                                pdfZoomMultiplier = 1.0;
                                if (pdfcZoomInput) pdfcZoomInput.value = 100;
                                renderStandardPdfPage(pdfPageNum);
                            });
                        });
                    </script>

                <?php else: ?>
                    <!-- VISOR ESTÁNDAR (PDF.js / Nativo) - EL QUE YA TENÍAS -->
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
                                    <button id="pdf-prev" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">◀ Anterior</button>
                                    <span class="text-slate-300">Página <strong id="pdf-page-num" class="text-slate-200">1</strong> de <strong id="pdf-page-count" class="text-slate-200">--</strong></span>
                                    <button id="pdf-next" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">Siguiente ▶</button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button id="pdf-zoom-out" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">🔍-</button>
                                    
                                    <!-- Cambiar <select id="pdfc-zoom-select"> o <select id="pdf-zoom-select"> por: -->
                                    <div class="flex items-center gap-1 bg-slate-800 border border-slate-700 rounded px-1 py-0.5">
                                        <input type="number" id="pdf-zoom-input" min="10" max="500" step="5" value="100" class="w-14 bg-transparent text-slate-200 font-mono text-xs text-center focus:outline-none">
                                        <span class="text-slate-400 font-mono text-xs pr-1">%</span>
                                    </div>

                                    <button id="pdf-zoom-in" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-3 py-1.5 rounded border border-slate-700 transition-colors">🔍+</button>
                                    
                                    <!-- Botón 100% exclusivo para PDF estándar -->
                                    <button id="pdf-zoom-reset" type="button" class="bg-slate-800 hover:bg-indigo-600 text-slate-200 px-2.5 py-1.5 rounded transition shadow font-medium" title="Restablecer a 100%">100%</button>
                                </div>
                            </div>

                            <!-- Contenedor con Panning (Arrastrar) y centrado -->
                            <div id="pdf-viewport-container" class="w-full h-[680px] bg-slate-950 rounded-lg border border-slate-700 overflow-hidden relative shadow-2xl cursor-grab">
                                <div id="pdf-viewport" class="absolute top-0 left-0 transition-transform duration-75 ease-out origin-top-left" style="transform: scale(1) translate(0px, 0px);">
                                    <canvas id="pdf-canvas" class="shadow-2xl rounded border border-slate-800 block"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- SUB-VISOR 2: VISOR NATIVO -->
                        <div id="viewer-native" class="w-full h-[650px]">
                            <iframe src="<?php echo e($urlWithThumbnails); ?>" class="w-full h-full rounded-lg border border-slate-700"></iframe>
                        </div>

                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const url = JSON.parse('<?php echo addslashes(json_encode($streamUrl)); ?>');
                            
                            let pdfDoc = null,
                                pageNum = 1,
                                pageRendering = false,
                                pageNumPending = null,
                                pdfZoomMultiplier = 1.0,
                                isScriptLoaded = false;

                            // Variables para Arrastrar / Panning
                            let pdfIsDragging = false, pdfStartX = 0, pdfStartY = 0, pdfPanX = 0, pdfPanY = 0;
                            let pdfCurrentScale = 1, pdfTranslateX = 0, pdfTranslateY = 0;

                            const toggleBtn = document.getElementById('pdf-toggle-viewer');
                            const pdfjsViewer = document.getElementById('viewer-pdfjs');
                            const nativeViewer = document.getElementById('viewer-native');
                            const pdfCanvas = document.getElementById('pdf-canvas');
                            const pdfCtx = pdfCanvas.getContext('2d');
                            const pdfContainerContainer = document.getElementById('pdf-viewport-container');
                            const pdfViewportDiv = document.getElementById('pdf-viewport');

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
                                script.src = "<?php echo e(asset('js/vendor/pdf.min.js')); ?>";
                                script.async = true;
                                
                                script.onload = function() {
                                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = "<?php echo e(asset('js/vendor/pdf.worker.min.js')); ?>";
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

                            function updatePdfTransform() {
                                if (!pdfViewportDiv) return;
                                pdfViewportDiv.style.transform = `scale(${pdfCurrentScale}) translate(${pdfTranslateX + pdfPanX}px, ${pdfTranslateY + pdfPanY}px)`;
                            }

                            function renderPage(num) {
                                pageRendering = true;
                                pdfDoc.getPage(num).then(function(page) {
                                    // Renderizado interno a alta resolución
                                    const viewport = page.getViewport({ scale: 2.0 });
                                    pdfCanvas.height = viewport.height;
                                    pdfCanvas.width = viewport.width;

                                    const renderContext = {
                                        canvasContext: pdfCtx,
                                        viewport: viewport
                                    };
                                    page.render(renderContext).promise.then(function() {
                                    pageRendering = false;

                                    // Cálculos para encajar automáticamente al 100% de la altura/anchura del div
                                    const cW = pdfContainerContainer.clientWidth;
                                    const cH = pdfContainerContainer.clientHeight;
                                    
                                    const scaleX = cW / pdfCanvas.width;
                                    const scaleY = cH / pdfCanvas.height;
                                    const fitScale = Math.min(scaleX, scaleY);

                                    // Calculamos la escala y reseteamos el arrastre
                                    pdfCurrentScale = fitScale * pdfZoomMultiplier;
                                    pdfPanX = 0;
                                    pdfPanY = 0;

                                    // Centramos la imagen en el visor
                                    pdfTranslateX = (cW / 2) / pdfCurrentScale - (pdfCanvas.width / 2);
                                    pdfTranslateY = (cH / 2) / pdfCurrentScale - (pdfCanvas.height / 2);

                                    if (pdfZoomInput) {
                                        pdfZoomInput.value = Math.round(pdfZoomMultiplier * 100);
                                    }
                                    updatePdfTransform();
                                        if (pageNumPending !== null) {
                                            renderPage(pageNumPending);
                                            pageNumPending = null;
                                        }
                                    });
                                });

                                document.getElementById('pdf-page-num').textContent = num;
                                const zoomLevelElem = document.getElementById('pdf-zoom-level');
                                if (zoomLevelElem) {
                                    zoomLevelElem.textContent = Math.round(pdfZoomMultiplier * 100) + '%';
                                }
                            }

                            function queueRenderPage(num) {
                                if (pageRendering) {
                                    pageNumPending = num;
                                } else {
                                    renderPage(num);
                                }
                            }


                            document.getElementById('pdf-prev').addEventListener('click', () => {
                                if (!pdfDoc || pageNum <= 1) return;
                                pageNum--;
                                queueRenderPage(pageNum);
                            });

                            document.getElementById('pdf-next').addEventListener('click', () => {
                                if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
                                pageNum++;
                                queueRenderPage(pageNum);
                            });

                            // Controles de Zoom
                            document.getElementById('pdf-zoom-in').addEventListener('click', () => {
                                if (!pdfDoc) return;
                                pdfZoomMultiplier = Math.min(pdfZoomMultiplier + 0.2, 3.0);
                                queueRenderPage(pageNum);
                            });

                            document.getElementById('pdf-zoom-out').addEventListener('click', () => {
                                if (!pdfDoc || pdfZoomMultiplier <= 0.4) return;
                                pdfZoomMultiplier = Math.max(pdfZoomMultiplier - 0.2, 0.4);
                                queueRenderPage(pageNum);
                            });

                            // Para el PDF Estándar:
                            const pdfZoomInput = document.getElementById('pdf-zoom-input');
                            pdfZoomInput?.addEventListener('change', (e) => {
                                if (!pdfDoc) return;
                                let val = parseFloat(e.target.value);
                                if (isNaN(val) || val <= 0) val = 100;
                                pdfZoomMultiplier = val / 100;
                                queueRenderPage(pageNum);
                            });
                            // Botón de Reset 100% (actualiza también el input):
                            document.getElementById('pdf-zoom-reset')?.addEventListener('click', () => {
                                if (!pdfDoc) return;
                                pdfZoomMultiplier = 1.0;
                                if (pdfZoomInput) pdfZoomInput.value = "100";
                                queueRenderPage(pageNum);
                            });

                            // Eventos de Panning (Arrastrar)
                            if (pdfContainerContainer) {
                                pdfContainerContainer.addEventListener('mousedown', (e) => {
                                    pdfIsDragging = true;
                                    pdfStartX = e.clientX;
                                    pdfStartY = e.clientY;
                                    pdfContainerContainer.style.cursor = 'grabbing';
                                });

                                window.addEventListener('mousemove', (e) => {
                                    if (!pdfIsDragging) return;
                                    pdfPanX += (e.clientX - pdfStartX) / pdfCurrentScale;
                                    pdfPanY += (e.clientY - pdfStartY) / pdfCurrentScale;
                                    pdfStartX = e.clientX;
                                    pdfStartY = e.clientY;
                                    
                                    updatePdfTransform();
                                });

                                window.addEventListener('mouseup', () => {
                                    if (pdfIsDragging) {
                                        pdfIsDragging = false;
                                        pdfContainerContainer.style.cursor = 'grab';
                                    }
                                });

                                // Evitar que se quede "enganchado" al sacar el ratón
                                window.addEventListener('mouseleave', () => {
                                    if (pdfIsDragging) {
                                        pdfIsDragging = false;
                                        pdfContainerContainer.style.cursor = 'grab';
                                    }
                                });


                                // Zoom con la rueda del ratón (Ctrl + Scroll)
                                pdfContainerContainer.addEventListener('wheel', (e) => {
                                    if (e.ctrlKey) {
                                        e.preventDefault();
                                        if (e.deltaY < 0) {
                                            pdfZoomMultiplier = Math.min(pdfZoomMultiplier + 0.15, 4.0);
                                        } else {
                                            pdfZoomMultiplier = Math.max(pdfZoomMultiplier - 0.15, 0.4);
                                        }
                                        queueRenderPage(pageNum);
                                    }
                                }, { passive: false });
                            }
                        });
                    </script>
                <?php endif; ?>

            <!-- VISOR 3: DOCUMENTOS (Google Docs / Sheets vs Office Viewer) -->
            <?php elseif($type === 'document'): ?>
                <?php if($isGoogleDoc): ?>
                    <div class="w-full space-y-4">
                        <div class="flex justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs">
                            <span class="text-slate-300 font-semibold flex items-center gap-2">
                                📊 Documento de Google
                            </span>
                            <a href="<?php echo e($fileUrl); ?>" target="_blank" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-1.5 rounded-md transition-colors shadow flex items-center gap-1.5">
                                ↗️ Abrir y Editar en Google
                            </a>
                        </div>
                        <iframe src="<?php echo e($googleEmbedUrl); ?>" class="w-full h-[650px] rounded-lg border border-slate-700 bg-white"></iframe>
                    </div>
                <?php elseif($isOfficeDocument): ?>
                    <div class="w-full space-y-4">
                        <div class="flex justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs">
                            <span class="text-slate-300 font-semibold flex items-center gap-2">
                                📄 Documento [<?php echo e(strtoupper($extension)); ?>]
                            </span>
                        </div>
                        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=<?php echo e(urlencode($fileUrl)); ?>" class="w-full h-[650px] rounded-lg border border-slate-700"></iframe>
                    </div>
                <?php elseif($isTxtDocument): ?>
                    
                    
                    <div class="w-full space-y-4">
                        <div class="flex justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs">
                            <span class="text-slate-300 font-semibold flex items-center gap-2">
                                📄 Documento de Texto [<?php echo e(strtoupper($extension)); ?>]
                            </span>
                            
                            
                            <button 
                                id="btn-copy-txt"
                                onclick="copyTxtToClipboard()"
                                class="bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-300 font-medium px-3 py-1.5 rounded-md transition-colors flex items-center gap-1.5 active:scale-95"
                            >
                                <svg id="icon-copy" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                <svg id="icon-check" class="w-4 h-4 hidden text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span id="text-copy">Copiar</span>
                            </button>
                        </div>
                        
                        
                        <div class="w-full h-[650px] overflow-y-auto rounded-lg border border-slate-700 bg-slate-950 p-4 text-slate-100 font-mono text-sm">
                            
                            <pre id="txt-content" 
                                class="w-full text-left" 
                                style="white-space: pre-wrap !important; word-wrap: break-word !important; break-word: break-all !important; max-w-full !important; display: block !important;"
                            ><?php echo e($fileContent); ?></pre>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="w-full space-y-4">
                        <div class="flex justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs">
                            <span class="text-slate-300 font-semibold flex items-center gap-2">
                                🌐 Documento Web
                            </span>
                            <a href="<?php echo e($fileUrl); ?>" target="_blank" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-1.5 rounded-md transition-colors shadow flex items-center gap-1.5">
                                ↗️ Abrir en nueva pestaña
                            </a>
                        </div>
                        <iframe
                            src="<?php echo e($fileUrl); ?>"
                            class="w-full h-[650px] rounded-lg border border-slate-700 bg-white"
                            sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-modals"
                        ></iframe>
                    </div>
                <?php endif; ?>
            
            <?php elseif($type === 'audio'): ?>
                <div class="w-full max-w-md p-6 bg-slate-900 rounded-xl text-center space-y-4">
                    <p class="text-indigo-400 font-semibold">🎵 Reproductor de Audio</p>

                    <audio
                        id="resource-audio"
                        controls
                        preload="metadata"
                        class="w-full"
                    >
                        <source
                            src="<?php echo e($fileUrl); ?>"
                            type="<?php echo e($file->mime_type ?: 'audio/ogg'); ?>"
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

            <?php elseif($type === 'video'): ?>
                <video
                    controls
                    preload="metadata"
                    class="w-full max-h-[600px] rounded-lg border border-slate-700"
                >
                    <source
                        src="<?php echo e($fileUrl); ?>"
                        type="<?php echo e($file->mime_type ?: 'video/mp4'); ?>"
                    >
                    Tu navegador no puede reproducir este vídeo.
                </video>

            <!-- VISOR 6: ARCHIVOS COMPRIMIDOS (ZIP, TAR.GZ, TGZ, RAR, 7Z) -->
            <?php elseif($type === 'zip'): ?>
                <?php
                    $formatLabel = strtoupper($metadata['archive_format'] ?? 'Comprimido');
                    $treeList = !empty($fileTree) ? $fileTree : ($metadata['structure'] ?? []);
                    
                    // Comprobación real: Si es RAR/7Z y el árbol está completamente vacío, es que el servidor no ha podido leerlo
                    $isRarOr7z = in_array(strtolower($formatLabel), ['rar', '7z'], true);
                    $failedToProcess = $isRarOr7z && empty($treeList);
                ?>
                <div class="w-full space-y-4">
                    <div class="flex justify-between items-center bg-slate-900 p-3 rounded-lg border border-slate-700 text-xs">
                        <span class="text-indigo-300 font-bold flex items-center gap-2">
                            📦 Estructura del Archivo (<?php echo e($formatLabel); ?>)
                        </span>
                        <?php if(count($treeList) > 0): ?>
                            <span class="text-slate-300"><?php echo e(count($treeList)); ?> elementos encontrados</span>
                        <?php endif; ?>
                    </div>

                    <?php if(count($treeList) > 0): ?>
                        <div class="bg-slate-900 rounded-lg p-4 font-mono text-sm max-h-[500px] overflow-y-auto space-y-1 border border-slate-700 select-text">
                            <?php $__currentLoopData = $treeList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
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
                                ?>
                                <div class="flex justify-between items-center py-1 border-b border-slate-800/50 hover:bg-slate-800/50 rounded px-2 transition-colors <?php echo e($item['is_dir'] ? 'text-indigo-300 font-bold' : 'text-slate-300'); ?> <?php echo e($paddingClass); ?>">
                                    
                                    <span class="truncate pr-4 flex items-center gap-2">
                                        <span class="text-base leading-none"><?php echo e($item['is_dir'] ? '📁' : '📄'); ?></span>
                                        <span class="font-semibold"><?php echo e($item['name']); ?></span>
                                        <span class="text-slate-400 font-normal text-xs hidden md:inline">(<?php echo e($cleanPath); ?>)</span>
                                    </span>

                                    <span class="text-slate-400 text-xs shrink-0 font-mono">
                                        <?php if($item['is_dir']): ?>
                                            <span class="bg-indigo-950 text-indigo-400 text-[10px] px-1.5 py-0.5 rounded border border-indigo-800/40">Carpeta</span>
                                        <?php else: ?>
                                            <?php echo e($item['size'] > 0 ? number_format($item['size'] / 1024, 1) . ' KB' : '0 KB'); ?>

                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="p-6 bg-slate-900 rounded-lg border border-slate-700 text-center space-y-3">
                            <p class="text-slate-300 text-sm">Contenido empaquetado en formato <?php echo e($formatLabel); ?>.</p>
                            
                            <?php if($failedToProcess): ?>
                                <p class="text-amber-400 text-xs italic bg-amber-950/40 border border-amber-500/30 p-3 rounded-lg max-w-xl mx-auto">
                                    ⚠️ Nota: La previsualización de directorios para archivos .RAR/.7Z no está disponible en este servidor debido a limitaciones técnicas de la infraestructura. Puedes descargar el archivo para inspeccionar o extraer su contenido.
                                </p>
                            <?php endif; ?>
                            
                            <?php if($fileUrl): ?>
                                <a href="<?php echo e($fileUrl); ?>" download target="_blank" class="inline-block bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-2.5 rounded-lg text-sm transition-colors shadow">
                                    Descargar <?php echo e($formatLabel); ?> Completo
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <!-- VISOR 2: MAPA INTERACTIVO -->
        <?php elseif($resource->type === 'map'): ?>
            <?php
                $map = $resource->resourceable;
                $mapImageUrl = $map ? $map->map_image_url : null;
                $markers = $map ? ($map->markers ?? []) : [];
            ?>

            <?php if($mapImageUrl): ?>
                <div class="w-full space-y-4">
                    <div id="map-viewer" class="w-full h-[600px] bg-slate-900 border border-slate-700 rounded-lg overflow-hidden"></div>
                </div>

                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const mapUrl = JSON.parse('<?php echo addslashes(json_encode($mapImageUrl)); ?>');
                        const markersList = JSON.parse('<?php echo addslashes(json_encode($markers)); ?>');

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
            <?php else: ?>
                <p class="text-slate-300">No se pudo cargar la imagen del mapa.</p>
            <?php endif; ?>

        <!-- VISOR 3: FICHAS, DIARIOS Y CAMPAÑAS -->
        <?php elseif(in_array($resource->type, ['sheet', 'diary', 'campaign'])): ?>
            <div class="w-full space-y-4 text-slate-200">
                <?php if($resource->resourceable && isset($resource->resourceable->content['blocks'])): ?>
                    <?php $__currentLoopData = $resource->resourceable->content['blocks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($block['type'] === 'header'): ?>
                            <?php
                                $level = min(max((int)($block['data']['level'] ?? 2), 1), 6);
                                $tag = "h{$level}";
                                
                                $headerClasses = match($level) {
                                    1 => 'text-3xl font-bold text-indigo-400 mt-6 mb-2',
                                    2 => 'text-2xl font-bold text-indigo-400 mt-5 mb-2',
                                    3 => 'text-xl font-bold text-indigo-300 mt-4 mb-2',
                                    4 => 'text-lg font-bold text-indigo-300 mt-3 mb-1 underline',
                                    5 => 'text-base font-semibold text-slate-200 mt-3 mb-1',
                                    6 => 'text-sm font-semibold text-slate-300 mt-2 mb-1 italic',
                                    default => 'text-2xl font-bold text-indigo-400 mt-5 mb-2',
                                };
                            ?>
                            <<?php echo e($tag); ?> class="<?php echo e($headerClasses); ?>"><?php echo $block['data']['text']; ?></<?php echo e($tag); ?>>
                        <?php elseif($block['type'] === 'paragraph'): ?>
                            <p class="text-slate-300 leading-relaxed"><?php echo $block['data']['text']; ?></p>
                        <?php elseif($block['type'] === 'image'): ?>
                            <?php
                                $imageUrl = $block['data']['url'] ?? $block['data']['file']['url'] ?? null;
                            ?>
                            <?php if($imageUrl): ?>
                                <div class="my-4 text-center">
                                    <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($block['data']['caption'] ?? ''); ?>" class="max-h-[500px] mx-auto rounded-lg border border-slate-700 shadow-md">
                                    <?php if(!empty($block['data']['caption'])): ?>
                                        <p class="text-xs text-slate-400 mt-1 italic"><?php echo e($block['data']['caption']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php elseif($block['type'] === 'table'): ?>
                            <?php
                                $withHeadings = $block['data']['withHeadings'] ?? false;
                                $rows = $block['data']['content'] ?? [];
                            ?>
                            <div class="overflow-x-auto my-4">
                                <table class="w-full text-sm text-left border border-slate-700">
                                    <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($loop->first && $withHeadings): ?>
                                            <thead>
                                                <tr class="bg-slate-900 border-b border-slate-700 text-indigo-300 font-bold uppercase text-xs">
                                                    <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <th class="p-3 border-r border-slate-700"><?php echo $cell; ?></th>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                        <?php else: ?>
                                            <tr class="border-b border-slate-700/60 hover:bg-slate-800/40">
                                                <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <td class="p-3 border-r border-slate-700/60"><?php echo $cell; ?></td>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($withHeadings && count($rows) > 0): ?>
                                        </tbody>
                                    <?php endif; ?>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <p class="text-slate-300 italic">Este recurso no contiene bloques de contenido.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php $__env->stopSection(); ?>

<script>
    // Definimos la función directamente en el objeto global window para asegurar su acceso
    window.copyTxtToClipboard = function() {
        const textElement = document.getElementById('txt-content');
        if (!textElement) return;

        const textToCopy = textElement.innerText;
        const btn = document.getElementById('btn-copy-txt');
        const iconCopy = document.getElementById('icon-copy');
        const iconCheck = document.getElementById('icon-check');

        function showSuccessState() {
            if(iconCopy) iconCopy.classList.add('hidden');
            if(iconCheck) iconCheck.classList.remove('hidden');
            if(btn) btn.classList.add('text-emerald-400', 'border-emerald-600');

            setTimeout(() => {
                if(iconCopy) iconCopy.classList.remove('hidden');
                if(iconCheck) iconCheck.classList.add('hidden');
                if(btn) btn.classList.remove('text-emerald-400', 'border-emerald-600');
            }, 2000);
        }

        // 1. Intentar con la API moderna si está disponible (HTTPS / localhost)
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textToCopy)
                .then(showSuccessState)
                .catch(err => console.error('Error al copiar: ', err));
        } else {
            // 2. Alternativa (Fallback) clásica compatible con entornos HTTP no seguros
            const textArea = document.createElement('textarea');
            textArea.value = textToCopy;
            textArea.style.position = 'fixed'; // Evita scroll visual en la pantalla
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                showSuccessState();
            } catch (err) {
                console.error('Fallback fallido: ', err);
                alert('No se pudo copiar automáticamente en este navegador.');
            }

            document.body.removeChild(textArea);
        }
    };
</script>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/ricardo/workspace/ocanyaweb/corrol/resources/views/resources/show.blade.php ENDPATH**/ ?>
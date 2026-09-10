@extends('layouts.app')

@section('title', 'Mapeador de Cómic: ' . $resource->title)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-wrap justify-between items-center bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-xl gap-4">
        <div>
            <h1 class="text-2xl font-bold text-indigo-400">🖼️ Mapeador de Cómic</h1>
            <p class="text-slate-300 text-sm mt-1">Dibuja, edita y reordena las viñetas para la lectura guiada.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('resources.show', $resource) }}" target="_blank" class="bg-teal-600 hover:bg-teal-500 text-white font-bold py-2 px-4 rounded-lg transition shadow-lg" title="Ver resultado en una nueva pestaña">
                👀 Ver Resultado
            </a>
            <a href="{{ route('resources.edit', $resource) }}" class="bg-slate-700 hover:bg-slate-600 text-white font-bold py-2 px-4 rounded-lg transition">Volver</a>
            <button id="btn-save" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 px-4 rounded-lg transition shadow-lg">
                💾 Guardar Metadatos
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Panel Izquierdo: Herramientas y Lista -->
        <div class="lg:col-span-1 bg-slate-800 rounded-xl p-4 border border-slate-700 shadow-xl space-y-4">
            <!-- Navegación -->
            <div class="flex items-center justify-between">
                <button id="prev-page" class="bg-slate-700 hover:bg-slate-600 px-3 py-1 rounded text-white text-sm">◀ Pág</button>
                <div class="flex items-center gap-1 text-slate-300 font-bold text-sm">
                    <span>Pág</span>
                    <select id="page-select" class="bg-slate-700 text-white font-bold text-sm rounded px-2 py-0.5 border border-slate-600 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                        <option value="1">1</option>
                    </select>
                    <span>/<span id="page-count">--</span></span>
                </div>
                <button id="next-page" class="bg-slate-700 hover:bg-slate-600 px-3 py-1 rounded text-white text-sm">Pág ▶</button>
            </div>
            
            <hr class="border-slate-700">

            <!-- Selector de Modos/Herramientas -->
            <div>
                <label class="block text-xs font-bold uppercase text-indigo-300 mb-2">Modo de Trabajo</label>
                <div class="grid grid-cols-3 gap-1 bg-slate-900 p-1 rounded-lg text-xs">
                    <button id="tool-rect" class="tool-btn active py-1.5 rounded font-semibold text-slate-300 hover:text-white transition">🔲 Rect</button>
                    <button id="tool-poly" class="tool-btn py-1.5 rounded font-semibold text-slate-300 hover:text-white transition">⬡ Polígono</button>
                    <button id="tool-select" class="tool-btn py-1.5 rounded font-semibold text-slate-300 hover:text-white transition">👆 Editar</button>
                </div>
                <p id="tool-help" class="text-[11px] text-slate-400 mt-2 italic">Haz clic y arrastra para crear un rectángulo.</p>
            </div>

            <hr class="border-slate-700">

            <!-- Lista de Viñetas y Reordenación -->
            <div>
                <h3 class="font-bold text-indigo-300 text-sm mb-2">Viñetas en esta página:</h3>
                <ul id="panels-list" class="space-y-2 text-sm text-slate-300 max-h-[350px] overflow-y-auto pr-1">
                    <!-- Elementos generados dinámicamente -->
                </ul>
                <p id="empty-msg" class="text-xs text-slate-500 italic">No hay viñetas trazadas en esta página.</p>
            </div>

            <button id="btn-clear" class="w-full bg-rose-600/20 text-rose-400 border border-rose-500/50 hover:bg-rose-600 hover:text-white py-1.5 rounded transition text-sm mb-4">
                🗑️ Borrar viñetas de esta pág.
            </button>

            <!-- Historial y Atajos de Teclado -->
            <div class="bg-slate-900 border border-slate-700 rounded-lg p-3">
                <h4 class="text-xs font-bold text-indigo-300 uppercase mb-2">⌨️ Atajos y Acción</h4>
                <ul class="text-[11px] text-slate-400 space-y-1 mb-3">
                    <li><strong class="text-slate-200">[Ctrl + Z]</strong> Deshacer</li>
                    <li><strong class="text-slate-200">[Ctrl + Y]</strong> Rehacer</li>
                    <li><strong class="text-slate-200">[Supr]</strong> Borrar Vértice/Viñeta</li>
                </ul>
                
                <hr class="border-slate-700 mb-2">
                
                <h4 class="text-xs font-bold text-indigo-300 uppercase mb-2">🕒 Historial Reciente</h4>
                <ul id="history-list" class="space-y-1 max-h-32 overflow-y-auto pr-1">
                    <!-- Se llenará desde JS -->
                </ul>
            </div>
        </div>

        <!-- Contenedor del Visor Sincronizado -->
        <div class="lg:col-span-3 bg-slate-900 rounded-xl border border-slate-700 shadow-xl overflow-auto flex items-center justify-center p-4 relative min-h-[600px]">
            
            <!-- Capa de Carga -->
            <div id="pdf-loading" class="absolute inset-0 bg-slate-900/90 backdrop-blur-sm flex flex-col items-center justify-center z-20 text-center p-6 transition-opacity">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent mb-4"></div>
                <p class="text-indigo-300 font-bold text-lg">Cargando PDF del Cómic...</p>
            </div>

            <!-- Contenedor Estricto con Posicionamiento Relativo -->
            <div id="canvas-wrapper" class="relative shadow-2xl rounded bg-black">
                <canvas id="pdf-canvas" class="block"></canvas>
                <canvas id="draw-canvas" class="absolute inset-0 z-10 cursor-crosshair"></canvas>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('styles')
<style>
    .tool-btn.active {
        background-color: #4f46e5;
        color: #ffffff;
    }
</style>
@endpush

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        const url = "{{ route('comic.stream', $resource->id) }}";
        const loadingOverlay = document.getElementById('pdf-loading');
        
        const pdfCanvas = document.getElementById('pdf-canvas');
        const pdfCtx = pdfCanvas.getContext('2d');
        const drawCanvas = document.getElementById('draw-canvas');
        const drawCtx = drawCanvas.getContext('2d');
        const canvasWrapper = document.getElementById('canvas-wrapper');
        const pageSelect = document.getElementById('page-select');

        let pdfDoc = null, pageNum = 1, currentRenderTask = null, scale = 1.5;
        
        let allPanels = {!! json_encode($resource->comic_metadata ?? new stdClass()) !!};
        if (Array.isArray(allPanels)) allPanels = {};

        // --- SISTEMA DE HISTORIAL AVANZADO ---
        let historyStack = [];
        let historyIndex = -1;

        function saveState(actionName = 'Acción') {
            if (historyIndex < historyStack.length - 1) {
                historyStack = historyStack.slice(0, historyIndex + 1);
            }
            
            historyStack.push({
                action: actionName,
                data: JSON.stringify(allPanels)
            });
            
            if (historyStack.length > 30) {
                historyStack.shift(); 
            } else {
                historyIndex++;
            }
            renderHistoryUI();
        }

        function loadHistoryState(index) {
            if (index >= 0 && index < historyStack.length) {
                historyIndex = index;
                allPanels = JSON.parse(historyStack[historyIndex].data);
                selectedPanelIndex = -1;
                activeVertexIndex = -1;
                updatePanelsList();
                redrawOverlay();
                renderHistoryUI();
            }
        }

        function undo() {
            if (historyIndex > 0) loadHistoryState(historyIndex - 1);
        }

        function redo() {
            if (historyIndex < historyStack.length - 1) loadHistoryState(historyIndex + 1);
        }

        function renderHistoryUI() {
            const list = document.getElementById('history-list');
            if (!list) return;
            list.innerHTML = '';
            
            let start = Math.max(0, historyStack.length - 10);
            for (let i = historyStack.length - 1; i >= start; i--) {
                const item = historyStack[i];
                const isActive = (i === historyIndex);
                const isFuture = (i > historyIndex);
                
                let classes = 'text-xs px-2 py-1 rounded cursor-pointer transition truncate border border-transparent ';
                
                if (isActive) {
                    classes += 'bg-indigo-600 text-white font-bold border-indigo-500 shadow-md';
                } else if (isFuture) {
                    classes += 'text-slate-500 hover:bg-slate-700/50 hover:text-slate-300 border-slate-700/30 border-dashed';
                } else {
                    classes += 'text-slate-300 hover:bg-slate-700/80';
                }

                list.innerHTML += `<li class="${classes}" onclick="goToHistory(${i})" title="Saltar a este punto">
                    ${isActive ? '👉 ' : (isFuture ? '⏭️ ' : '⏳ ')}${item.action}
                </li>`;
            }
        }

        window.goToHistory = function(index) {
            loadHistoryState(index);
        };

        saveState('Estado inicial cargado');

        // --- ESTADOS DEL EDITOR ---
        let currentMode = 'rect';
        let isDrawing = false;
        let startPos = null;
        let polyPoints = [];
        let selectedPanelIndex = -1;
        let draggingVertexIndex = -1;
        let activeVertexIndex = -1;
        let isDraggingPanel = false;
        let dragStartPos = null;
        let hasDragged = false;

        // Cargar PDF
        window.pdfjsLib.getDocument(url).promise.then(function(pdf) {
            pdfDoc = pdf;
            document.getElementById('page-count').textContent = pdf.numPages;
            
            // Poblar el select de páginas dinámicamente
            pageSelect.innerHTML = '';
            for (let i = 1; i <= pdf.numPages; i++) {
                pageSelect.innerHTML += `<option value="${i}">${i}</option>`;
            }

            renderPage(pageNum);
        }).catch(function(error) {
            console.error('Error al cargar PDF:', error);
            loadingOverlay.innerHTML = `<p class="text-rose-500 font-bold">❌ Error al cargar el PDF</p>`;
        });

        function renderPage(num) {
            if (currentRenderTask) currentRenderTask.cancel();

            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({ scale: scale });

                pdfCanvas.width = viewport.width;
                pdfCanvas.height = viewport.height;
                drawCanvas.width = viewport.width;
                drawCanvas.height = viewport.height;

                const displayWidth = Math.floor(viewport.width);
                const displayHeight = Math.floor(viewport.height);

                canvasWrapper.style.width = `${displayWidth}px`;
                canvasWrapper.style.height = `${displayHeight}px`;
                pdfCanvas.style.width = `${displayWidth}px`;
                pdfCanvas.style.height = `${displayHeight}px`;
                drawCanvas.style.width = `${displayWidth}px`;
                drawCanvas.style.height = `${displayHeight}px`;

                const renderContext = { canvasContext: pdfCtx, viewport: viewport };
                currentRenderTask = page.render(renderContext);

                currentRenderTask.promise.then(function() {
                    currentRenderTask = null;
                    if (loadingOverlay) loadingOverlay.classList.add('hidden');
                    redrawOverlay();
                }).catch(function(err) {
                    if (err.name !== 'RenderingCancelledException') console.error('Error rendering page:', err);
                });
            });

            pageSelect.value = num;
            polyPoints = [];
            selectedPanelIndex = -1;
            activeVertexIndex = -1;
            updatePanelsList();
        }

        // Evento para cambiar de página desde el selector
        pageSelect.addEventListener('change', (e) => {
            const selectedPage = parseInt(e.target.value);
            if (selectedPage && selectedPage >= 1 && selectedPage <= pdfDoc.numPages) {
                pageNum = selectedPage;
                renderPage(pageNum);
            }
        });

        function getMousePosNormalized(evt) {
            const rect = drawCanvas.getBoundingClientRect();
            return {
                x: (evt.clientX - rect.left) / rect.width,
                y: (evt.clientY - rect.top) / rect.height
            };
        }

        function redrawOverlay(mousePos = null) {
            drawCtx.clearRect(0, 0, drawCanvas.width, drawCanvas.height);
            const panels = allPanels[pageNum] || [];

            panels.forEach((panel, i) => {
                const isSelected = (i === selectedPanelIndex);
                drawPanelShape(panel, i + 1, isSelected);
            });

            if (currentMode === 'rect' && isDrawing && startPos && mousePos) {
                drawCtx.strokeStyle = '#4f46e5';
                drawCtx.lineWidth = 2;
                drawCtx.setLineDash([6, 6]);
                drawCtx.strokeRect(
                    startPos.x * drawCanvas.width, startPos.y * drawCanvas.height,
                    (mousePos.x - startPos.x) * drawCanvas.width, (mousePos.y - startPos.y) * drawCanvas.height
                );
                drawCtx.setLineDash([]);
            } else if (currentMode === 'poly' && polyPoints.length > 0) {
                drawCtx.strokeStyle = '#6366f1';
                drawCtx.lineWidth = 2;
                drawCtx.beginPath();
                polyPoints.forEach((pt, idx) => {
                    const px = pt.x * drawCanvas.width, py = pt.y * drawCanvas.height;
                    if (idx === 0) drawCtx.moveTo(px, py); else drawCtx.lineTo(px, py);
                });
                if (mousePos) drawCtx.lineTo(mousePos.x * drawCanvas.width, mousePos.y * drawCanvas.height);
                drawCtx.stroke();

                polyPoints.forEach(pt => {
                    drawCtx.fillStyle = '#818cf8';
                    drawCtx.beginPath();
                    drawCtx.arc(pt.x * drawCanvas.width, pt.y * drawCanvas.height, 5, 0, Math.PI * 2);
                    drawCtx.fill();
                });
            }
        }

        function drawPanelShape(panel, number, isSelected) {
            const points = getPanelPoints(panel);
            if (points.length === 0) return;

            drawCtx.beginPath();
            points.forEach((pt, idx) => {
                const px = pt.x * drawCanvas.width, py = pt.y * drawCanvas.height;
                if (idx === 0) drawCtx.moveTo(px, py); else drawCtx.lineTo(px, py);
            });
            drawCtx.closePath();

            drawCtx.fillStyle = isSelected ? 'rgba(99, 102, 241, 0.25)' : 'rgba(16, 185, 129, 0.15)';
            drawCtx.fill();
            drawCtx.strokeStyle = isSelected ? '#6366f1' : '#10b981';
            drawCtx.lineWidth = isSelected ? 3 : 2;
            drawCtx.stroke();

            const firstPt = points[0];
            const badgeX = firstPt.x * drawCanvas.width, badgeY = firstPt.y * drawCanvas.height;
            drawCtx.fillStyle = isSelected ? '#6366f1' : '#10b981';
            drawCtx.fillRect(badgeX, badgeY, 22, 22);
            drawCtx.fillStyle = '#ffffff';
            drawCtx.font = 'bold 13px sans-serif';
            drawCtx.fillText(number, badgeX + 6, badgeY + 16);

            if (isSelected && currentMode === 'select') {
                points.forEach((pt, idx) => {
                    const isVertexActive = (idx === activeVertexIndex);
                    drawCtx.fillStyle = isVertexActive ? '#fbbf24' : '#ffffff';
                    drawCtx.strokeStyle = isVertexActive ? '#d97706' : '#4f46e5';
                    drawCtx.lineWidth = 2;
                    drawCtx.beginPath();
                    drawCtx.arc(pt.x * drawCanvas.width, pt.y * drawCanvas.height, isVertexActive ? 8 : 6, 0, Math.PI * 2);
                    drawCtx.fill();
                    drawCtx.stroke();
                });
            }
        }

        function getPanelPoints(panel) {
            if (panel.type === 'polygon' && panel.points) return panel.points;
            return [
                { x: panel.x, y: panel.y },
                { x: panel.x + panel.w, y: panel.y },
                { x: panel.x + panel.w, y: panel.y + panel.h },
                { x: panel.x, y: panel.y + panel.h }
            ];
        }

        function ensurePolygon(panel) {
            if (panel.type !== 'polygon') {
                panel.type = 'polygon';
                panel.points = getPanelPoints(panel);
                delete panel.x; delete panel.y; delete panel.w; delete panel.h;
            }
            return panel;
        }

        function distToSegment(p, v, w) {
            const l2 = (v.x - w.x) ** 2 + (v.y - w.y) ** 2;
            if (l2 === 0) return Math.hypot(p.x - v.x, p.y - v.y);
            let t = ((p.x - v.x) * (w.x - v.x) + (p.y - v.y) * (w.y - v.y)) / l2;
            t = Math.max(0, Math.min(1, t));
            return Math.hypot(p.x - (v.x + t * (w.x - v.x)), p.y - (v.y + t * (w.y - v.y)));
        }

        function findEdgeAtPos(pos, points) {
            const threshold = 8;
            for (let i = 0; i < points.length; i++) {
                const p1 = points[i], p2 = points[(i + 1) % points.length];
                const p1Px = { x: p1.x * drawCanvas.width, y: p1.y * drawCanvas.height };
                const p2Px = { x: p2.x * drawCanvas.width, y: p2.y * drawCanvas.height };
                const mousePx = { x: pos.x * drawCanvas.width, y: pos.y * drawCanvas.height };
                if (distToSegment(mousePx, p1Px, p2Px) < threshold) return i;
            }
            return -1;
        }

        // --- EVENTOS DEL TECLADO ---
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'z') {
                e.preventDefault();
                undo();
            }
            if (e.ctrlKey && (e.key === 'y' || e.key === 'Y')) {
                e.preventDefault();
                redo();
            }

            if ((e.key === 'Delete' || e.key === 'Backspace') && currentMode === 'select' && selectedPanelIndex !== -1) {
                if (activeVertexIndex !== -1) {
                    let panel = ensurePolygon(allPanels[pageNum][selectedPanelIndex]);
                    if (panel.points.length > 3) {
                        panel.points.splice(activeVertexIndex, 1);
                        activeVertexIndex = -1;
                        saveState('Borrar vértice'); 
                        redrawOverlay();
                    } else {
                        alert('Un polígono necesita al menos 3 vértices para existir.');
                    }
                } else {
                    allPanels[pageNum].splice(selectedPanelIndex, 1);
                    selectedPanelIndex = -1;
                    updatePanelsList();
                    saveState('Eliminar viñeta (teclado)');
                    redrawOverlay();
                }
            }
        });

        // --- EVENTOS DEL CANVAS ---
        drawCanvas.addEventListener('mousedown', (e) => {
            const pos = getMousePosNormalized(e);

            if (currentMode === 'rect') {
                isDrawing = true;
                startPos = pos;
            } else if (currentMode === 'poly') {
                polyPoints.push(pos);
                redrawOverlay(pos);
            } else if (currentMode === 'select') {
                if (selectedPanelIndex !== -1) {
                    let panel = allPanels[pageNum][selectedPanelIndex];
                    const points = getPanelPoints(panel);

                    const vIdx = points.findIndex(pt => {
                        const dx = (pt.x - pos.x) * drawCanvas.width;
                        const dy = (pt.y - pos.y) * drawCanvas.height;
                        return Math.sqrt(dx * dx + dy * dy) < 10;
                    });

                    if (vIdx !== -1) {
                        draggingVertexIndex = vIdx;
                        activeVertexIndex = vIdx;
                        hasDragged = false;
                        redrawOverlay();
                        return;
                    } else {
                        activeVertexIndex = -1;
                    }

                    const edgeIdx = findEdgeAtPos(pos, points);
                    if (edgeIdx !== -1) {
                        panel = ensurePolygon(panel);
                        panel.points.splice(edgeIdx + 1, 0, { x: pos.x, y: pos.y });
                        draggingVertexIndex = edgeIdx + 1;
                        activeVertexIndex = draggingVertexIndex;
                        saveState('Añadir vértice nuevo');
                        hasDragged = false;
                        redrawOverlay();
                        return;
                    }
                }

                const panels = allPanels[pageNum] || [];
                const foundIdx = panels.findIndex(p => isPointInPanel(pos, p));
                
                if (foundIdx !== -1) {
                    selectedPanelIndex = foundIdx;
                    isDraggingPanel = true;
                    dragStartPos = pos;
                    hasDragged = false;
                } else {
                    selectedPanelIndex = -1;
                }
                updatePanelsList();
                redrawOverlay();
            }
        });

        drawCanvas.addEventListener('mousemove', (e) => {
            const pos = getMousePosNormalized(e);

            if (currentMode === 'select') {
                if (draggingVertexIndex !== -1 && selectedPanelIndex !== -1) {
                    let panel = ensurePolygon(allPanels[pageNum][selectedPanelIndex]);
                    panel.points[draggingVertexIndex] = pos;
                    hasDragged = true;
                    redrawOverlay();
                    return;
                }

                if (isDraggingPanel && selectedPanelIndex !== -1 && dragStartPos) {
                    let panel = ensurePolygon(allPanels[pageNum][selectedPanelIndex]);
                    const dx = pos.x - dragStartPos.x;
                    const dy = pos.y - dragStartPos.y;

                    panel.points.forEach(pt => {
                        pt.x += dx;
                        pt.y += dy;
                    });

                    dragStartPos = pos;
                    hasDragged = true;
                    redrawOverlay();
                    return;
                }
            }

            if (isDrawing || (currentMode === 'poly' && polyPoints.length > 0)) {
                redrawOverlay(pos);
            }
        });

        drawCanvas.addEventListener('mouseup', (e) => {
            if (currentMode === 'select') {
                if (hasDragged) {
                    saveState(draggingVertexIndex !== -1 ? 'Mover vértice' : 'Mover viñeta');
                    hasDragged = false;
                }
                draggingVertexIndex = -1;
                isDraggingPanel = false;
                dragStartPos = null;
            }

            if (currentMode === 'rect' && isDrawing) {
                isDrawing = false;
                const endPos = getMousePosNormalized(e);
                const w = endPos.x - startPos.x, h = endPos.y - startPos.y;

                if (Math.abs(w) > 0.02 && Math.abs(h) > 0.02) {
                    if (!allPanels[pageNum]) allPanels[pageNum] = [];
                    allPanels[pageNum].push({
                        type: 'rect',
                        x: Math.min(startPos.x, endPos.x), y: Math.min(startPos.y, endPos.y),
                        w: Math.abs(w), h: Math.abs(h)
                    });
                    selectedPanelIndex = allPanels[pageNum].length - 1;
                    updatePanelsList();
                    saveState('Crear viñeta rectangular');
                }
                startPos = null;
                redrawOverlay();
            }
        });

        drawCanvas.addEventListener('dblclick', () => {
            if (currentMode === 'poly' && polyPoints.length >= 3) {
                if (!allPanels[pageNum]) allPanels[pageNum] = [];
                allPanels[pageNum].push({ type: 'polygon', points: [...polyPoints] });
                polyPoints = [];
                selectedPanelIndex = allPanels[pageNum].length - 1;
                updatePanelsList();
                saveState('Crear viñeta poligonal');
                redrawOverlay();
            }
        });

        function isPointInPanel(pt, panel) {
            const points = getPanelPoints(panel);
            let inside = false;
            for (let i = 0, j = points.length - 1; i < points.length; j = i++) {
                const xi = points[i].x, yi = points[i].y, xj = points[j].x, yj = points[j].y;
                const intersect = ((yi > pt.y) !== (yj > pt.y)) && (pt.x < (xj - xi) * (pt.y - yi) / (yj - yi) + xi);
                if (intersect) inside = !inside;
            }
            return inside;
        }

        const tools = {
            'rect': { btn: document.getElementById('tool-rect'), help: 'Haz clic y arrastra para crear un rectángulo.' },
            'poly': { btn: document.getElementById('tool-poly'), help: 'Haz clic para añadir vértices. Doble clic para cerrar la figura.' },
            'select': { btn: document.getElementById('tool-select'), help: 'Elige un vértice y pulsa [Supr] para borrar. Usa [Ctrl+Z] para deshacer cambios.' }
        };

        Object.keys(tools).forEach(mode => {
            tools[mode].btn.addEventListener('click', () => {
                currentMode = mode;
                polyPoints = [];
                
                Object.keys(tools).forEach(m => {
                    if (m === mode) {
                        tools[m].btn.classList.add('bg-indigo-600', 'text-white');
                        tools[m].btn.classList.remove('text-slate-300');
                    } else {
                        tools[m].btn.classList.remove('bg-indigo-600', 'text-white');
                        tools[m].btn.classList.add('text-slate-300');
                    }
                });

                document.getElementById('tool-help').textContent = tools[mode].help;
                drawCanvas.style.cursor = mode === 'select' ? 'default' : 'crosshair';
                redrawOverlay();
            });
        });

        document.getElementById('tool-rect').classList.add('bg-indigo-600', 'text-white');
        document.getElementById('tool-rect').classList.remove('text-slate-300');

        function updatePanelsList() {
            const list = document.getElementById('panels-list');
            const msg = document.getElementById('empty-msg');
            const panels = allPanels[pageNum] || [];
            
            list.innerHTML = '';
            if (panels.length > 0) {
                msg.classList.add('hidden');
                panels.forEach((p, i) => {
                    const activeBg = (i === selectedPanelIndex) ? 'bg-indigo-900/60 border-indigo-500' : 'bg-slate-900 border-slate-700';
                    const typeLabel = p.type === 'polygon' ? '⬡ Polígono' : '🔲 Rect';
                    
                    list.innerHTML += `
                        <li class="flex items-center justify-between ${activeBg} border p-2 rounded transition cursor-pointer" onclick="selectPanelFromList(${i})">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-indigo-400">#${i + 1}</span>
                                <span class="text-xs text-slate-400">${typeLabel}</span>
                            </div>
                            <div class="flex items-center gap-1" onclick="event.stopPropagation()">
                                <button onclick="duplicatePanel(${i})" class="px-1.5 py-0.5 bg-indigo-900/40 hover:bg-indigo-600 text-indigo-300 hover:text-white rounded text-xs" title="Duplicar">📋</button>
                                <button onclick="movePanel(${i}, -1)" class="px-1.5 py-0.5 bg-slate-800 hover:bg-slate-700 rounded text-xs" title="Subir">▲</button>
                                <button onclick="movePanel(${i}, 1)" class="px-1.5 py-0.5 bg-slate-800 hover:bg-slate-700 rounded text-xs" title="Bajar">▼</button>
                                <button onclick="deletePanel(${i})" class="px-1.5 py-0.5 bg-rose-900/40 hover:bg-rose-600 text-rose-300 hover:text-white rounded text-xs" title="Eliminar">🗑️</button>
                            </div>
                        </li>`;
                });
            } else {
                msg.classList.remove('hidden');
            }
        }

        window.selectPanelFromList = function(index) {
            selectedPanelIndex = index;
            activeVertexIndex = -1;
            updatePanelsList();
            redrawOverlay();
        };

        window.duplicatePanel = function(index) {
            const panels = allPanels[pageNum];
            const original = panels[index];
            let copy = JSON.parse(JSON.stringify(original));

            const offset = 0.02;
            if (copy.type === 'rect') {
                copy.x += offset; copy.y += offset;
            } else if (copy.points) {
                copy.points = copy.points.map(pt => ({ x: pt.x + offset, y: pt.y + offset }));
            }

            panels.splice(index + 1, 0, copy);
            selectedPanelIndex = index + 1;
            updatePanelsList();
            saveState('Duplicar viñeta');
            redrawOverlay();
        };

        window.movePanel = function(index, direction) {
            const panels = allPanels[pageNum];
            const targetIndex = index + direction;
            if (targetIndex < 0 || targetIndex >= panels.length) return;
            
            const temp = panels[index];
            panels[index] = panels[targetIndex];
            panels[targetIndex] = temp;

            selectedPanelIndex = targetIndex;
            updatePanelsList();
            saveState('Reordenar capas');
            redrawOverlay();
        };

        window.deletePanel = function(index) {
            allPanels[pageNum].splice(index, 1);
            if (selectedPanelIndex === index) {
                selectedPanelIndex = -1;
                activeVertexIndex = -1;
            }
            updatePanelsList();
            saveState('Eliminar viñeta (botón)');
            redrawOverlay();
        };

        document.getElementById('prev-page').addEventListener('click', () => {
            if (!pdfDoc || pageNum <= 1) return;
            pageNum--;
            renderPage(pageNum);
        });

        document.getElementById('next-page').addEventListener('click', () => {
            if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
            pageNum++;
            renderPage(pageNum);
        });

        document.getElementById('btn-clear').addEventListener('click', () => {
            allPanels[pageNum] = [];
            selectedPanelIndex = -1;
            activeVertexIndex = -1;
            updatePanelsList();
            saveState('Vaciar toda la página');
            redrawOverlay();
        });

        document.getElementById('btn-save').addEventListener('click', () => {
            const btn = document.getElementById('btn-save');
            btn.innerHTML = '⏳ Guardando...';
            
            fetch("{{ route('comic.metadata.update', $resource->id) }}", {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ metadata: allPanels })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '✅ ¡Guardado!';
                    setTimeout(() => btn.innerHTML = '💾 Guardar Metadatos', 2000);
                }
            })
            .catch(err => {
                console.error(err);
                btn.innerHTML = '❌ Error';
                setTimeout(() => btn.innerHTML = '💾 Guardar Metadatos', 2000);
            });
        });
    });
</script>
@endsection
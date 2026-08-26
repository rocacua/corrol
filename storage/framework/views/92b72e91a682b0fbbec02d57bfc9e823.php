<?php $__env->startSection('title', 'Crear/Editar ' . ucfirst($type) . ' - CorRol'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto space-y-6">
    
    <?php echo $__env->make('partials.creation-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 shadow-lg">
                <h3 class="font-bold text-sm text-indigo-400 mb-3 flex justify-between items-center">
                    <span>Mis <?php echo e(ucfirst($type)); ?>s</span>
                    <a href="<?php echo e(route($type === 'sheet' ? 'sheets.create' : ($type === 'diary' ? 'diaries.create' : 'campaigns.create'))); ?>" class="text-xs text-emerald-400 hover:underline">+ Nuevo</a>
                </h3>

                <div class="space-y-2 max-h-[500px] overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $userResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="?edit_id=<?php echo e($res->id); ?>" 
                           class="block p-3 rounded-lg border text-xs transition-all <?php echo e(($editingResource && $editingResource->id === $res->id) ? 'bg-indigo-950 border-indigo-500 text-white font-bold' : 'bg-slate-900 border-slate-700/60 text-slate-300 hover:border-indigo-500'); ?>">
                            <div class="truncate"><?php echo e($res->title); ?></div>
                            <div class="text-[10px] text-slate-400 mt-1"><?php echo e($res->created_at->format('d/m/Y')); ?></div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-xs text-slate-400 italic">No tienes ningún registro aún.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl space-y-6">
            <h1 class="text-2xl font-bold text-indigo-400">
                <?php if(!empty($isClone)): ?>
                    📋 Clonar <?php echo e(ucfirst($type)); ?> (Nueva Copia)
                <?php elseif($editingResource): ?>
                    ✏️ Editar <?php echo e(ucfirst($type)); ?>

                <?php else: ?>
                    📝 Crear Nuevo/a <?php echo e(ucfirst($type)); ?>

                <?php endif; ?>
            </h1>

            <form id="sheet-form" action="<?php echo e(route('sheets.store')); ?>" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                
                <?php if($editingResource && empty($isClone)): ?>
                    <input type="hidden" name="resource_id" value="<?php echo e($editingResource->id); ?>">
                <?php endif; ?>
                <input type="hidden" name="type" value="<?php echo e($type); ?>">
                <input type="hidden" name="content_json" id="content_json">

                <?php
                    $defaultTitle = $editingResource ? (!empty($isClone) ? '[Copia] ' . $editingResource->title : $editingResource->title) : '';
                ?>
                <div>
                    <label class="block text-sm font-medium mb-2">Título *</label>
                    <input type="text" name="title" value="<?php echo e(old('title', $defaultTitle)); ?>" required 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <!-- Descripción del Recurso -->
                <div>
                    <label class="block text-sm font-medium mb-2">Descripción</label>
                    <textarea name="description" rows="3" placeholder="Resumen o notas sobre este recurso..."
                              class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500"><?php echo e(old('description', $editingResource->description ?? '')); ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Juego de Rol</label>
                        <input type="text" name="game" list="games-list" placeholder="Ej: RuneQuest" value="<?php echo e(old('game', $editingResource->game ?? '')); ?>"
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                        <datalist id="games-list">
                            <?php $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($g); ?>">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Campaña</label>
                        <input type="text" name="campaign" list="campaigns-list" placeholder="Ej: Juego de Dioses" value="<?php echo e(old('campaign', $editingResource->campaign ?? '')); ?>"
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                        <datalist id="campaigns-list">
                            <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($c); ?>">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Privacidad</label>
                    <select name="privacy" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none">
                        <option value="public" <?php echo e(old('privacy', $editingResource->privacy ?? 'public') === 'public' ? 'selected' : ''); ?>>Público</option>
                        <option value="private" <?php echo e(old('privacy', $editingResource->privacy ?? 'public') === 'private' ? 'selected' : ''); ?>>Privado</option>
                    </select>
                </div>


                <!-- Etiquetas (Tags) -->
                <div>
                    <label class="block text-sm font-medium mb-2">Etiquetas (Separadas por comas)</label>
                    <input type="text" id="tags-input" name="tags" placeholder="ej: pj, nivel-5, campaña-principal"
                           value="<?php echo e(old('tags', isset($editingResource->tags) ? implode(', ', $editingResource->tags) : '')); ?>"
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                    
                    <?php if(!empty($allTags)): ?>
                    <div  class="mt-2 max-h-[60px] overflow-y-auto block">
                        <div class="mt-2 flex flex-wrap gap-2 items-center text-xs text-slate-300">
                            <span class="font-semibold text-slate-400">Sugerencias:</span>
                            <?php $__currentLoopData = $allTags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" data-tag="<?php echo e($tag); ?>" 
                                        class="tag-suggestion-btn bg-slate-700 hover:bg-indigo-600 text-slate-300 hover:text-white px-2 py-1 rounded-md transition-colors cursor-pointer">
                                    + <?php echo e($tag); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Contenido de la Ficha / Diario *</label>
                    <div id="editorjs" data-content='<?php echo json_encode($editingResource && $editingResource->resourceable ? ($editingResource->resourceable->content ?? new \stdClass()) : new \stdClass()); ?>' class="bg-slate-900 border border-slate-700 rounded-lg p-4 min-h-[350px] text-slate-100 cursor-text"></div>
                </div>

                <button type="button" onclick="submitSheetForm()" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg transition-colors shadow-lg">
                    Guardar <?php echo e(ucfirst($type)); ?>

                </button>
            </form>
        </div>

    </div>

    <!-- ESTILOS DE TEMA OSCURO Y FIX DE MENÚS Y TABLAS PARA EDITOR.JS -->
    <style>
        /* Espacio libre al final para trabajar cómodamente */
        #editorjs, 
        .codex-editor, 
        .codex-editor__redactor {
            overflow: visible !important;
            padding-bottom: 140px !important;
        }

        /* Garantizar que los bloques no atrapen los desplegables */
        .ce-block,
        .ce-block__content {
            position: relative;
            z-index: auto !important;
        }

        /* Prioridad absoluta para menús flotantes, desplegables y barra '+' */
        .ce-popover, 
        .ce-popover__container,
        .tc-popover, 
        .ce-toolbar,
        .ce-toolbar__content,
        .ce-inline-toolbar, 
        .ce-conversion-toolbar {
            z-index: 999999 !important;
        }

        /* Barras de herramientas e íconos flotantes del '+' */
        .ce-toolbar__plus, 
        .ce-toolbar__settings-btn, 
        .tc-toolbox__toggler {
            color: #e2e8f0 !important;
            background-color: #1e293b !important;
            border: 1px solid #475569 !important;
            border-radius: 6px !important;
        }

        .ce-toolbar__plus:hover, 
        .ce-toolbar__settings-btn:hover, 
        .tc-toolbox__toggler:hover {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }

        /* Menús emergentes desplegables (Popovers) */
        .ce-popover,
        .ce-popover__container, 
        .tc-popover, 
        .ce-inline-toolbar, 
        .ce-conversion-toolbar {
            background-color: #0f172a !important;
            background: #0f172a !important;
            border: 1px solid #334155 !important;
            color: #f8fafc !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.9) !important;
            border-radius: 8px !important;
        }

         /* Campo de Búsqueda dentro del menú del '+' */
        .ce-popover__search,
        .ce-popover__search-input,
        .ce-popover-header__search-input {
            background-color: #1e293b !important;
            background: #1e293b !important;
            color: #f8fafc !important;
            border: 1px solid #475569 !important;
            border-radius: 6px !important;
        }

        .ce-popover__search-input::placeholder {
            color: #94a3b8 !important;
        }

        /* Elementos dentro de los desplegables */
        .ce-popover__item, 
        .tc-popover__item,  
        .ce-popover-item,
        .ce-inline-tool, 
        .ce-conversion-tool {
            color: #cbd5e1 !important;
            background-color: transparent !important;
            background: transparent !important;
        }

        .ce-popover__item:hover, 
        .tc-popover__item:hover, 
        .ce-popover-item:hover,
        .ce-popover-item--active,
        .ce-inline-tool:hover, 
        .ce-conversion-tool:hover,
        .ce-popover__item--active, 
        .tc-popover__item--active {
            background-color: #1e293b !important;
            background: #1e293b !important;
            color: #818cf8 !important;
        }

        /* Texto y Títulos en menús */
        .ce-popover__item-label, 
        .tc-popover__item-label, 
        .tc-popover__item-title,
        .ce-popover-item__title, 
        .ce-popover__custom-content {
            color: #f1f5f9 !important;
        }

        /* Íconos dentro de menús */
        .ce-popover__item-icon, 
        .tc-popover__item-icon,
        .ce-popover-item__icon, 
        .ce-inline-tool svg, 
        .ce-conversion-tool__icon,
        .tc-popover__item svg,
        .ce-popover-item svg {
            color: #94a3b8 !important;
            fill: currentColor !important;
        }

        .ce-popover__item:hover .ce-popover__item-icon, 
        .tc-popover__item:hover .tc-popover__item-icon,
        .ce-popover-item:hover .ce-popover-item__icon,
        .ce-popover__item:hover svg, 
        .tc-popover__item:hover svg {
            color: #818cf8 !important;
            fill: #818cf8 !important;
        }

        /* Estilos específicos para las tablas */
        .tc-table {
            border-color: #334155 !important;
        }

        .tc-row {
            border-bottom: 1px solid #334155 !important;
        }

        .tc-cell {
            border-right: 1px solid #334155 !important;
            color: #f8fafc !important;
        }

        .tc-cell--selected {
            background-color: rgba(99, 102, 241, 0.25) !important;
        }

        /* 🛠️ ENCABEZADOS DE TABLA EN EL EDITOR (Texto negro sobre fondo blanco) */
        .tc-table--heading .tc-row:first-child .tc-cell,
        .tc-table--heading .tc-row:first-child,
        .tc-row:first-child.tc-row--header .tc-cell {
            background-color: #ffffff !important;
            color: #0f172a !important;
            font-weight: bold !important;
        }

        .tc-table--heading .tc-row:first-child .tc-cell .tc-table__inp {
            color: #0f172a !important;
            font-weight: bold !important;
        }


        .cdx-input, 
        .tc-table__inp {
            color: #f8fafc !important;
        }
        .tc-add-row {
            margin-bottom: 1rem !important;
        }

        /* Estilos específicos para encabezados */
        .ce-header {
            font-weight: bold;
        }
        h1.ce-header {
            color:  var(--color-indigo-400);
            font-weight: bold;
            font-size: var(--text-3xl);
        }
        h2.ce-header {
            color:  var(--color-indigo-400);
            font-weight: bold;
            font-size: var(--text-2xl);
        }
        h3.ce-header {
            font-size: var(--text-lg);
        }
        h4.ce-header {
            font-size: var(--text-base);
            text-decoration: underline;
        }
        h5.ce-header {
            font-size: var(--text-base);
        }
        h6.ce-header {
            font-size: var(--text-base);
            font-style: italic;
        }

    </style>

    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/table@latest"></script>

    <script>
        // 🛠️ TUNE PERSONALIZADO: PERMITE DUPLICAR CUALQUIER BLOQUE (TABLAS, TEXTOS, IMÁGENES)
        class DuplicateTune {
            static get isTune() {
                return true;
            }

            constructor({ api, block }) {
                this.api = api;
                this.block = block;
            }

            render() {
                const button = document.createElement('div');
                button.className = 'ce-popover__item';
                button.style.cssText = 'display: flex; align-items: center; gap: 8px; padding: 6px 10px; cursor: pointer; color: #cbd5e1;';
                button.innerHTML = `
                    <div class="ce-popover__item-icon" style="color: #94a3b8; font-size: 14px;">📋</div>
                    <div class="ce-popover__item-label" style="color: #f1f5f9; font-size: 13px; font-weight: 500;">Duplicar Bloque</div>
                `;
                button.addEventListener('mouseenter', () => {
                    button.style.backgroundColor = '#1e293b';
                    button.style.color = '#818cf8';
                });
                button.addEventListener('mouseleave', () => {
                    button.style.backgroundColor = 'transparent';
                    button.style.color = '#cbd5e1';
                });
                
                button.addEventListener('click', () => {
                    const index = this.api.blocks.getCurrentBlockIndex();
                    this.api.blocks.getBlockByIndex(index).save().then(savedData => {
                        this.api.blocks.insert(savedData.tool, savedData.data, {}, index + 1, true);
                    }).catch(err => console.error('Error duplicando bloque:', err));
                });

                return button;
            }
        }

        // Plugin de Imagen por URL o Archivo optimizado para CorRol
        class CustomImageTool {
            static get toolbox() {
                return {
                    title: 'Imagen (URL o Archivo)',
                    icon: '<svg width="17" height="15" viewBox="0 0 336 276" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M291 150V79c0-19-15-34-34-34H79c-19 0-34 15-34 34v42l67-44 81 54 56-37 42 30zm0 37l-43-30-56 37-82-54-65 43v24c0 19 15 34 34 34h178c19 0 34-15 34-34v-20z"/></svg>'
                };
            }

            constructor({data}) {
                this.data = data || {};
            }

            render() {
                const container = document.createElement('div');
                container.className = 'p-4 bg-slate-950 border border-slate-700 rounded-lg space-y-3';

                const label = document.createElement('label');
                label.className = 'block text-xs font-semibold text-slate-300';
                label.innerText = '🖼️ Pegar URL de Imagen:';

                const inputUrl = document.createElement('input');
                inputUrl.type = 'url';
                inputUrl.placeholder = 'https://ejemplo.com/mi_imagen.png';
                inputUrl.className = 'w-full bg-slate-900 border border-slate-700 rounded p-2.5 text-xs text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500';
                inputUrl.value = this.data.url || (this.data.file ? this.data.file.url : '') || '';

                const captionInput = document.createElement('input');
                captionInput.type = 'text';
                captionInput.placeholder = 'Leyenda / Descripción de la imagen (opcional)';
                captionInput.className = 'w-full bg-slate-900 border border-slate-700 rounded p-2.5 text-xs text-slate-400 outline-none';
                captionInput.value = this.data.caption || '';

                const imgPreview = document.createElement('img');
                imgPreview.className = 'max-h-64 mx-auto rounded border border-slate-800 mt-2 object-contain ' + (inputUrl.value ? '' : 'hidden');
                if (inputUrl.value) imgPreview.src = inputUrl.value;

                inputUrl.addEventListener('input', () => {
                    this.data.url = inputUrl.value;
                    if (inputUrl.value) {
                        imgPreview.src = inputUrl.value;
                        imgPreview.classList.remove('hidden');
                    } else {
                        imgPreview.classList.add('hidden');
                    }
                });

                captionInput.addEventListener('input', () => {
                    this.data.caption = captionInput.value;
                });

                container.appendChild(label);
                container.appendChild(inputUrl);
                container.appendChild(captionInput);
                container.appendChild(imgPreview);

                return container;
            }

            save(blockContent) {
                const inputs = blockContent.querySelectorAll('input');
                return {
                    url: inputs[0] ? inputs[0].value : '',
                    caption: inputs[1] ? inputs[1].value : ''
                };
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const editorEl = document.getElementById('editorjs');
            const rawContent = JSON.parse(editorEl.getAttribute('data-content') || '{}');

            // 🛠️ EVITAR PÉRDIDA DE FOCO AL HACER CLIC EN LOS MENÚS DE TABLA (PERMITE ELIMINAR LA ÚLTIMA FILA)
            document.addEventListener('mousedown', function (e) {
                if (e.target.closest('.ce-popover, .tc-popover, .tc-toolbox, .ce-toolbar')) {
                    e.preventDefault();
                }
            }, true);

            const tools = {
                duplicateTune: DuplicateTune
            };
            if (typeof Header !== 'undefined') {
                tools.header = {
                    class: Header,
                    inlineToolbar: true
                };
            }
            if (typeof Table !== 'undefined') {
                tools.table = {
                    class: Table,
                    inlineToolbar: true
                };
            }

            // Usamos nuestro plugin de Imagen por URL
            tools.image = {
                class: CustomImageTool,
                inlineToolbar: true
            };

            window.editor = new EditorJS({
                holder: 'editorjs',
                data: rawContent,
                tools: tools,
                tunes: ['duplicateTune'],
                placeholder: 'Haz clic aquí para empezar a escribir tu Ficha o Diario...'
            });

            // 🛠️ MEJORA: INTERCEPTAR EVENTO "PASTE" EN TABLAS
            editorEl.addEventListener('paste', function (e) {
                const cell = e.target.closest('.tc-cell, .tc-table__inp');
                const table = e.target.closest('.tc-table');

                if (cell && table) {
                    // Detener la captura global de Editor.js que expulsa el texto fuera de la tabla
                    e.stopPropagation();

                    const clipboardData = e.clipboardData || window.clipboardData;
                    if (!clipboardData) return;

                    const pastedText = clipboardData.getData('text/plain');
                    if (!pastedText) return;

                    // CASO A: Si el texto contiene tabulaciones (\t), es una tabla copiada (Excel / Google Sheets)
                    if (pastedText.includes('\t')) {
                        e.preventDefault();
                        const rows = pastedText.trim().split(/\r?\n/).map(row => row.split('\t'));
                        
                        const tableRows = Array.from(table.querySelectorAll('.tc-row'));
                        const startRow = tableRows.findIndex(r => r.contains(cell));
                        
                        if (startRow !== -1) {
                            const startRowCells = Array.from(tableRows[startRow].querySelectorAll('.tc-cell'));
                            const startCol = startRowCells.findIndex(c => c === cell || c.contains(cell));

                            rows.forEach((rowData, rIdx) => {
                                const targetRow = tableRows[startRow + rIdx];
                                if (targetRow) {
                                    const targetCells = Array.from(targetRow.querySelectorAll('.tc-cell'));
                                    rowData.forEach((cellData, cIdx) => {
                                        const targetCell = targetCells[startCol + cIdx];
                                        if (targetCell) {
                                            targetCell.innerText = cellData.trim();
                                        }
                                    });
                                }
                            });
                        }
                        return;
                    }

                    // CASO B: Texto normal dentro de la misma celda
                    e.preventDefault();
                    const selection = window.getSelection();
                    if (selection.rangeCount) {
                        selection.deleteFromDocument();
                        const textNode = document.createTextNode(pastedText);
                        const range = selection.getRangeAt(0);
                        range.insertNode(textNode);
                        range.setStartAfter(textNode);
                        range.setEndAfter(textNode);
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }
                }
            }, true); // Fases de captura activada (useCapture = true)
        });

        function submitSheetForm() {
            if (window.editor) {
                window.editor.save().then(function(outputData) {
                    document.getElementById('content_json').value = JSON.stringify(outputData);
                    document.getElementById('sheet-form').submit();
                }).catch(function(error) {
                    console.error('Error guardando Editor.js:', error);
                    alert('Ocurrió un error al procesar el contenido del editor.');
                });
            } else {
                document.getElementById('sheet-form').submit();
            }
        }
    </script>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/ricardo/workspace/ocanyaweb/corrol/resources/views/sheets/form.blade.php ENDPATH**/ ?>
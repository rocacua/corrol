<?php $__env->startSection('title', 'Explorar Recursos - CorRol'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    
    <form method="GET" action="<?php echo e(route('resources.index')); ?>" id="advanced-search" class="space-y-6">
        
        <!-- ENCABEZADO CON BÚSQUEDA GENERAL DE ACCESO RÁPIDO -->
        <div class="flex flex-wrap justify-between items-center gap-4 bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-xl">
            <h1 class="text-3xl font-bold text-indigo-400 shrink-0">🎲 Explorador de Recursos</h1>
            
            <div class="flex flex-wrap items-center gap-3 md:w-auto flex-1 md:flex-initial justify-end">
                <!-- Campo Búsqueda General -->
                <div class="relative sm:w-64">
                    <input type="text" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="🔍 Buscar por cualquier campo..." 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-3 text-xs text-slate-100 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2 rounded-lg transition-colors shadow">
                    Buscar
                </button>

                <button type="button" onclick="document.getElementById('advanced-filters-panel').classList.toggle('hidden')" 
                        class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-bold px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                    ⚙️ Búsqueda Avanzada / Ordenar
                </button>
            </div>
        </div>

        <!-- PANEL DE BÚSQUEDA AVANZADA (DESPLEGABLE) -->
        <div id="advanced-filters-panel" 
              class="<?php echo e(!empty(array_filter(array_diff_key($filters, ['q' => '']))) ? '' : 'hidden'); ?> bg-slate-800 p-6 rounded-xl border border-slate-700 space-y-4 shadow-xl">
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Tipo de Recurso -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tipo de Recurso:</label>
                    <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                        <option value="">-- Todos los Tipos --</option>
                        <option value="file" <?php echo e(($filters['type'] ?? '') === 'file' ? 'selected' : ''); ?>>Archivo / Documento</option>
                        <option value="sheet" <?php echo e(($filters['type'] ?? '') === 'sheet' ? 'selected' : ''); ?>>Ficha de PJ</option>
                        <option value="diary" <?php echo e(($filters['type'] ?? '') === 'diary' ? 'selected' : ''); ?>>Diario de Sesión</option>
                        <option value="campaign" <?php echo e(($filters['type'] ?? '') === 'campaign' ? 'selected' : ''); ?>>Campaña</option>
                        <option value="map" <?php echo e(($filters['type'] ?? '') === 'map' ? 'selected' : ''); ?>>Mapa Interactivo</option>
                    </select>
                </div>

                <!-- Juego -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Juego de Rol:</label>
                    <input type="text" name="game" list="games-list" value="<?php echo e($filters['game'] ?? ''); ?>" placeholder="Ej: D&D 5e" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                    <datalist id="games-list">
                        <?php $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <option value="<?php echo e($g); ?>"> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </datalist>
                </div>

                <!-- Campaña -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Campaña:</label>
                    <input type="text" name="campaign" list="campaigns-list" value="<?php echo e($filters['campaign'] ?? ''); ?>" placeholder="Nombre de campaña" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                    <datalist id="campaigns-list">
                        <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <option value="<?php echo e($c); ?>"> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </datalist>
                </div>

                <!-- Autor -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Autor Original:</label>
                    <input type="text" name="author" list="authors-list" value="<?php echo e($filters['author'] ?? ''); ?>" placeholder="Nombre del autor" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                    <datalist id="authors-list">
                        <?php $__currentLoopData = $authors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <option value="<?php echo e($a); ?>"> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </datalist>
                </div>

                <!-- Etiqueta (Tag) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Etiqueta (#Tag):</label>
                    <input type="text" name="tag" value="<?php echo e($filters['tag'] ?? ''); ?>" placeholder="Ej: mapa" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                </div>

                <!-- Fecha Desde -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Publicado desde:</label>
                    <input type="date" name="date_from" value="<?php echo e($filters['date_from'] ?? ''); ?>" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                </div>

                <!-- Fecha Hasta -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Publicado hasta:</label>
                    <input type="date" name="date_to" value="<?php echo e($filters['date_to'] ?? ''); ?>" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                </div>

                <!-- Criterio de Ordenación -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Ordenar por:</label>
                    <select name="sort" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-xs text-slate-100">
                        <option value="latest" <?php echo e(($filters['sort'] ?? '') === 'latest' ? 'selected' : ''); ?>>Más recientes primero</option>
                        <option value="oldest" <?php echo e(($filters['sort'] ?? '') === 'oldest' ? 'selected' : ''); ?>>Más antiguos primero</option>
                        <option value="title_asc" <?php echo e(($filters['sort'] ?? '') === 'title_asc' ? 'selected' : ''); ?>>Título (A-Z)</option>
                        <option value="title_desc" <?php echo e(($filters['sort'] ?? '') === 'title_desc' ? 'selected' : ''); ?>>Título (Z-A)</option>
                        <option value="type" <?php echo e(($filters['sort'] ?? '') === 'type' ? 'selected' : ''); ?>>Tipo de Recurso</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-slate-700/60">
                <a href="<?php echo e(route('resources.index')); ?>" class="bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs font-bold px-4 py-2 rounded-lg">
                    Limpiar Filtros
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-6 py-2 rounded-lg shadow">
                    🔍 Aplicar Filtros
                </button>
            </div>
        </div>
    </form>

    <!-- LISTADO DE RESULTADOS -->
    <?php if($resources->count() > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 shadow-lg flex flex-col justify-between hover:border-indigo-500 transition-colors">
                    <div>
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <span class="px-2.5 py-0.5 bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-[10px] font-bold rounded-full uppercase">
                                <?php echo e($resource->type); ?>

                            </span>
                            <span class="text-xs <?php echo e($resource->privacy === 'private' ? 'text-amber-400' : 'text-emerald-400'); ?>">
                                <?php echo e(ucfirst($resource->privacy)); ?>

                                <br /><span class="text-[10px] text-slate-400"><?php echo e($resource->created_at->format('d/m/Y')); ?></span>
                            </span>
                            <span class="text-xs text-slate-300">
                                <strong class="text-slate-200">
                                    <?php echo e($resource->game ?? 'General'); ?><br />
                                    <?php echo e($resource->campaign ?? ''); ?><br />
                                    <?php echo e($resource->author ?? 'Anónimo'); ?>

                                </strong>
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-100 hover:text-indigo-400 transition-colors">
                            <a href="<?php echo e(route('resources.show', $resource->id)); ?>"><?php echo e($resource->title); ?></a>
                        </h3>
                        <?php if($resource->description): ?>
                            <p class="text-slate-400 text-xs mt-2 line-clamp-2"><?php echo e($resource->description); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
                        <span><strong class="text-slate-300" title="Subido por <?php echo e($resource->user->name ?? 'Anónimo'); ?>"> <?php echo e($resource->user->name ?? 'Anónimo'); ?></strong></span>
                        <a href="<?php echo e(route('resources.show', $resource->id)); ?>" class="text-indigo-400 hover:underline font-semibold">
                            Ver Recurso →
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="mt-6">
            <?php echo e($resources->links()); ?>

        </div>
    <?php else: ?>
        <div class="bg-slate-800 rounded-xl p-12 text-center border border-slate-700">
            <p class="text-slate-400">No se encontraron recursos con los filtros aplicados.</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/ricardo/workspace/ocanyaweb/corrol/resources/views/resources/index.blade.php ENDPATH**/ ?>
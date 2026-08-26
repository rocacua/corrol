<?php $__env->startSection('title', 'Favoritos de ' . $targetUser->name . ' - CorRol'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-wrap justify-between items-center gap-4 bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-xl">
        <div>
            <h1 class="text-2xl font-bold text-slate-100 flex items-center gap-2">
                ⭐ Recursos Favoritos de <?php echo e($targetUser->name); ?>

            </h1>
            <p class="text-slate-400 text-sm mt-1">
                Explora la colección de recursos guardados por <?php echo e($targetUser->name); ?>.
            </p>
        </div>

        <!-- Selector de Ordenación -->
        <form method="GET" action="<?php echo e(route('favorites.index', $targetUser->id)); ?>" class="flex items-center gap-2">
            <label for="sort" class="text-xs font-semibold text-slate-300">Ordenar por:</label>
            <select name="sort" id="sort" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700 rounded-lg text-xs p-2 text-slate-200 outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="latest" <?php echo e(($filters['sort'] ?? '') === 'latest' ? 'selected' : ''); ?>>Más recientes primero</option>
                <option value="oldest" <?php echo e(($filters['sort'] ?? '') === 'oldest' ? 'selected' : ''); ?>>Más antiguos primero</option>
                <option value="title_asc" <?php echo e(($filters['sort'] ?? '') === 'title_asc' ? 'selected' : ''); ?>>Título (A-Z)</option>
                <option value="title_desc" <?php echo e(($filters['sort'] ?? '') === 'title_desc' ? 'selected' : ''); ?>>Título (Z-A)</option>
            </select>
        </form>
    </div>

    <!-- Grid de Recursos -->
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
                        <span>🎲 <?php echo e($resource->game ?? 'General'); ?></span>
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
            <p class="text-slate-400">Este usuario no tiene ningún recurso guardado en sus favoritos.</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/ricardo/workspace/ocanyaweb/corrol/resources/views/favorites/index.blade.php ENDPATH**/ ?>
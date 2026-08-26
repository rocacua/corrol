<?php $__env->startSection('title', 'Usuarios y Autores - CorRol'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <h1 class="text-3xl font-bold text-indigo-400">👥 Creadores</h1>
    <p class="text-slate-300 text-sm">Listado de usuarios registrados en la plataforma.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 flex items-center justify-between gap-3 shadow-lg">
                
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shrink-0">
                        <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                    </span>
                    <div class="truncate">
                        <h3 class="font-bold text-base text-slate-100 truncate"><?php echo e($user->name); ?></h3>
                        <p class="text-xs text-slate-300 truncate"><?php echo e($user->resources_count); ?> <?php echo e($user->resources_count === 1 ? 'recurso público' : 'recursos públicos'); ?></p>
                    </div>
                </div>

                
                <div class="flex flex-col gap-1.5 shrink-0 text-xs font-bold">
                    <a href="<?php echo e(route('resources.index', ['user_id' => $user->id])); ?>" class="bg-slate-700 hover:bg-indigo-600 text-slate-200 hover:text-white px-3 py-1.5 rounded-lg transition-colors text-center">
                        Recursos
                    </a>
                    <a href="<?php echo e(route('favorites.index', $user->id)); ?>" class="bg-slate-700 hover:bg-amber-600 text-slate-200 hover:text-white px-3 py-1.5 rounded-lg transition-colors text-center">
                        ⭐ Favoritos
                    </a>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-slate-400 col-span-3 text-center py-8">No hay usuarios registrados.</p>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/ricardo/workspace/ocanyaweb/corrol/resources/views/users/index.blade.php ENDPATH**/ ?>
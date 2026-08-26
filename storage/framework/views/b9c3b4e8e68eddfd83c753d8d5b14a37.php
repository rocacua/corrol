<?php $__env->startSection('title', 'Campañas de Rol - CorRol'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <h1 class="text-3xl font-bold text-indigo-400">📜 Listado de Campañas</h1>
    <p class="text-slate-300 text-sm">Explora las campañas de rol creadas por la comunidad.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('resources.index', ['campaign' => $campaign])); ?>" 
               class="bg-slate-800 p-6 rounded-xl border border-slate-700 hover:border-indigo-500 text-center font-bold text-lg text-slate-200 hover:text-indigo-400 transition-all shadow-md">
                📜 <?php echo e($campaign); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-slate-400 col-span-4 text-center py-8">Aún no hay campañas registradas.</p>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/ricardo/workspace/ocanyaweb/corrol/resources/views/campaigns/index.blade.php ENDPATH**/ ?>
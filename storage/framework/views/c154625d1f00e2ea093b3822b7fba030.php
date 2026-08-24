<div class="mb-8 border-b border-slate-700 pb-4">
    <label class="block text-xs font-semibold uppercase text-slate-300 mb-3">¿Qué deseas crear o subir?</label>
    <div class="flex flex-wrap gap-2 text-xs font-bold">
        <a href="<?php echo e(route('resources.create')); ?>" 
           class="px-4 py-2.5 rounded-lg border transition-all flex items-center gap-1.5 <?php echo e(request()->routeIs('resources.create') ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg' : 'bg-slate-900 border-slate-700 text-slate-300 hover:border-indigo-500'); ?>">
            📁 Archivo / URL
        </a>
        <a href="<?php echo e(auth()->check() ? route('sheets.create') : route('sheets.guest')); ?>" 
           class="px-4 py-2.5 rounded-lg border transition-all flex items-center gap-1.5 <?php echo e(request()->routeIs('sheets.*') ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg' : 'bg-slate-900 border-slate-700 text-slate-300 hover:border-indigo-500'); ?>">
            📋 Ficha de PNJ
        </a>
        <a href="<?php echo e(auth()->check() ? route('diaries.create') : route('sheets.guest')); ?>" 
           class="px-4 py-2.5 rounded-lg border transition-all flex items-center gap-1.5 <?php echo e(request()->routeIs('diaries.*') ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg' : 'bg-slate-900 border-slate-700 text-slate-300 hover:border-indigo-500'); ?>">
            📖 Diario de Sesión
        </a>
        <a href="<?php echo e(auth()->check() ? route('maps.create') : route('sheets.guest')); ?>" 
           class="px-4 py-2.5 rounded-lg border transition-all flex items-center gap-1.5 <?php echo e(request()->routeIs('maps.*') ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg' : 'bg-slate-900 border-slate-700 text-slate-300 hover:border-indigo-500'); ?>">
            🗺️ Mapa Interactivo
        </a>
        <a href="<?php echo e(auth()->check() ? route('campaigns.create') : route('sheets.guest')); ?>" 
           class="px-4 py-2.5 rounded-lg border transition-all flex items-center gap-1.5 <?php echo e(request()->routeIs('campaigns.create') ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg' : 'bg-slate-900 border-slate-700 text-slate-300 hover:border-indigo-500'); ?>">
            🏰 Campaña Completa
        </a>
    </div>
</div><?php /**PATH /home/ricardo/workspace/ocanyaweb/corrol/resources/views/partials/creation-nav.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Subir Recurso - CorRol'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto bg-slate-800 rounded-xl p-8 shadow-2xl border border-slate-700">
    
    <!-- BARRA DE NAVEGACIÓN ENTRE TIPOS DE CREACIÓN -->
    <?php echo $__env->make('partials.creation-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <h1 class="text-3xl font-bold mb-2 text-indigo-400">🎲 Subir / Registrar Recurso</h1>
    <p class="text-slate-300 mb-6">Añade manuales, imágenes, audios, mapas o enlaces para tus campañas de rol.</p>

    <!-- Indicador de Almacenamiento -->
    <?php
        if ($usedBytes >= 1024 * 1024) {
            $formattedUsed = number_format($usedBytes / (1024 * 1024), 2) . ' MB';
        } else {
            $formattedUsed = number_format($usedBytes / 1024, 2) . ' KB';
        }
        $remainingMB = round($remainingBytes / (1024 * 1024), 2);
        $percentageUsed = min(100, round(($usedBytes / $totalLimitBytes) * 100, 2));
        $isLowStorage = $remainingMB < 100;
        $barWidth = max(0.5, $percentageUsed) . '%';
    ?>

    <div class="mb-6 p-4 rounded-lg bg-slate-900 border border-slate-700">
        <div class="flex justify-between items-center text-xs mb-1">
            <span class="text-slate-300">Espacio en la Nube (Compartido):</span>
            <span class="font-semibold <?php echo e($isLowStorage ? 'text-rose-400' : 'text-slate-300'); ?>">
                <?php echo e($formattedUsed); ?> de 10,240 MB usados (Quedan <?php echo e($remainingMB); ?> MB)
            </span>
        </div>
        <div class="w-full bg-slate-700 h-2.5 rounded-full overflow-hidden">
            <div id="storage-progress-bar" class="h-2.5 rounded-full <?php echo e($isLowStorage ? 'bg-rose-500' : 'bg-indigo-500'); ?>" data-width="<?php echo e(max(0.5, $percentageUsed)); ?>"></div>
        </div>
    </div>

    <?php if($errors->any()): ?>
        <div class="bg-rose-600/20 border border-rose-500 text-rose-300 p-4 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('warning')): ?>
        <div class="bg-amber-600/20 border border-amber-500 text-amber-300 p-4 rounded-lg mb-6 flex justify-between items-center">
            <div>
                <strong>⚠️ Aviso:</strong> <?php echo e(session('warning')); ?>

            </div>
            <?php if(session('existing_resource_id')): ?>
                <a href="<?php echo e(route('resources.show', session('existing_resource_id'))); ?>" 
                   class="bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs px-4 py-2 rounded-lg shadow transition-colors">
                    Ver "<?php echo e(session('existing_resource_title')); ?>" &rarr;
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if(session('success')): ?>
        <div class="bg-emerald-600/20 border border-emerald-500 text-emerald-300 p-4 rounded-lg mb-6 flex flex-wrap justify-between items-center gap-4">
            <div>
                <strong class="text-lg">¡Recurso registrado con éxito!</strong>
                <?php if(session('resource_title')): ?>
                    <div class="text-sm mt-1">Recurso: <em><?php echo e(session('resource_title')); ?></em></div>
                <?php endif; ?>
            </div>
            <?php if(session('resource_id')): ?>
                <a href="<?php echo e(route('resources.show', session('resource_id'))); ?>" 
                   class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm px-4 py-2.5 rounded-lg shadow transition-colors">
                    Ver Recurso Creado &rarr;
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <form
        id="resource-upload-form"
        action="<?php echo e(route('resources.store')); ?>"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        <?php echo csrf_field(); ?>

        <div>
            <label for="title-input" class="block text-sm font-medium mb-2">Título del Recurso *</label>
            <input type="text" id="title-input" name="title" value="<?php echo e(old('title')); ?>" required 
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <div>
            <label for="desc-input" class="block text-sm font-medium mb-2">Descripción</label>
            <textarea id="desc-input" name="description" rows="3" 
                      class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none"><?php echo e(old('description')); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="game-input" class="block text-sm font-medium mb-2">Juego de Rol</label>
                <input type="text" id="game-input" name="game" list="games-list" placeholder="Ej: RuneQuest, D&D 5e" value="<?php echo e(old('game')); ?>"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                <datalist id="games-list">
                    <?php $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($g); ?>">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </datalist>
            </div>

            <div>
                <label for="campaign-input" class="block text-sm font-medium mb-2">Campaña</label>
                <input type="text" id="campaign-input" name="campaign" list="campaigns-list" placeholder="Ej: Juego de Dioses" value="<?php echo e(old('campaign')); ?>"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                <datalist id="campaigns-list">
                    <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c); ?>">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </datalist>
            </div>

            <div>
                <label for="author-input" class="block text-sm font-medium mb-2">Autor Original</label>
                <input type="text" id="author-input" name="author" list="authors-list" placeholder="Ej: Greg Stafford" value="<?php echo e(old('author')); ?>"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                <datalist id="authors-list">
                    <?php $__currentLoopData = $authors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($a); ?>">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </datalist>
            </div>

            <div>
                <label for="privacy-select" class="block text-sm font-medium mb-2">Privacidad</label>
                <?php if(auth()->guard()->check()): ?>
                    <select id="privacy-select" name="privacy" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="public" <?php echo e(old('privacy') === 'public' ? 'selected' : ''); ?>>Público (Todos pueden verlo)</option>
                        <option value="private" <?php echo e(old('privacy') === 'private' ? 'selected' : ''); ?>>Privado (Solo accesible para mí)</option>
                    </select>
                <?php else: ?>
                    <input type="hidden" name="privacy" value="public">
                    <div class="bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-300 text-sm flex justify-between items-center">
                        <span>🌐 Público (Sin registro)</span>
                        <a href="<?php echo e(route('login')); ?>" class="text-xs text-indigo-400 hover:underline">Inicia sesión para privado</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <label for="tags-input" class="block text-sm font-medium mb-2">Palabras Clave (Separadas por comas)</label>
            <input type="text" id="tags-input" name="tags" placeholder="Ej: mapa, ciudad, pnj, pdf" value="<?php echo e(old('tags')); ?>"
                   class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
            
            <?php if(!empty($allTags)): ?>
                <div class="mt-2 flex flex-wrap gap-2 items-center text-xs text-slate-300">
                    <span class="font-semibold text-slate-400">Sugerencias:</span>
                    <?php $__currentLoopData = $allTags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" data-tag="<?php echo e($tag); ?>" 
                                class="tag-suggestion-btn bg-slate-700 hover:bg-indigo-600 text-slate-300 hover:text-white px-2 py-1 rounded-md transition-colors cursor-pointer">
                            + <?php echo e($tag); ?>

                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <hr class="border-slate-700 my-6">

        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-indigo-300">Origen del Recurso</h3>
            
            <div>
                <label for="file-input" class="block text-sm font-medium mb-2">Opción 1: Subir Archivo (PDF, Imagen, ZIP, Audio, Video)</label>
                <input type="file" id="file-input" name="file" 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
            </div>

            <div class="text-center text-xs text-slate-400 font-bold">- O BIEN -</div>

            <div>
                <label for="url-input" class="block text-sm font-medium mb-2">Opción 2: Introducir Enlace / URL Externa</label>
                <input type="url" id="url-input" name="external_url" placeholder="https://ejemplo.com/recurso.pdf" value="<?php echo e(old('external_url')); ?>"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
        </div>

        <button
            id="resource-submit-button"
            type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-600 disabled:cursor-not-allowed disabled:opacity-75 text-white font-bold py-3 px-6 rounded-lg transition-colors shadow-lg"
        >
            Guardar Recurso
        </button>
    </form>
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/ricardo/workspace/ocanyaweb/corrol/resources/views/resources/create.blade.php ENDPATH**/ ?>
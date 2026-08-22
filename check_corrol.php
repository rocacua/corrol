<?php
/**
 * Script de diagnóstico de requisitos para CorRol en Strato
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico CorRol - Strato</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-8">
    <div class="max-w-3xl mx-auto bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl">
        <h1 class="text-3xl font-bold text-indigo-400 mb-2">🎲 Diagnóstico CorRol</h1>
        <p class="text-slate-300 text-sm mb-6">Comprobando compatibilidad de requisitos en Strato...</p>

        <div class="space-y-4">
            
            <!-- 1. Versión de PHP -->
            <div class="p-4 bg-slate-900 rounded-lg border border-slate-700 flex justify-between items-center">
                <div>
                    <strong class="block text-sm">Versión de PHP</strong>
                    <span class="text-xs text-slate-300">Laravel 11+ requiere PHP >= 8.2</span>
                </div>
                <div>
                    <?php if (version_compare(PHP_VERSION, '8.2.0', '>=')): ?>
                        <span class="bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded text-xs font-bold">
                            Correcto (<?php echo PHP_VERSION; ?>)
                        </span>
                    <?php else: ?>
                        <span class="bg-rose-600/20 text-rose-400 border border-rose-500/30 px-3 py-1 rounded text-xs font-bold">
                            Error (<?php echo PHP_VERSION; ?>) - Cambia la versión en el panel de Strato
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. Extensiones Requeridas -->
            <div class="p-4 bg-slate-900 rounded-lg border border-slate-700">
                <strong class="block text-sm mb-3">Módulos PHP Obligatorios</strong>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <?php
                    $extensions = [
                        'pdo_mysql' => 'Conexión MySQL',
                        'mbstring' => 'Soporte UTF-8',
                        'xml' => 'Procesamiento XML',
                        'zip' => 'Clase ZipArchive',
                        'fileinfo' => 'Detección MIME-Type',
                        'gd' => 'Procesamiento de Imágenes',
                        'openssl' => 'Seguridad y S3 Backblaze',
                        'curl' => 'Peticiones HTTP (URLs externas)'
                    ];

                    foreach ($extensions as $ext => $desc):
                        $installed = extension_loaded($ext);
                    ?>
                        <div class="flex justify-between items-center p-2 rounded bg-slate-800 border border-slate-700/60">
                            <span class="text-slate-300"><?php echo $desc; ?> (<code><?php echo $ext; ?></code>)</span>
                            <span class="font-bold <?php echo $installed ? 'text-emerald-400' : 'text-rose-400'; ?>">
                                <?php echo $installed ? '✔ Sí' : '✘ No'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 3. Comprobar Symlink -->
            <div class="p-4 bg-slate-900 rounded-lg border border-slate-700 flex justify-between items-center">
                <div>
                    <strong class="block text-sm">Función Symlink (Enlace Simbólico)</strong>
                    <span class="text-xs text-slate-300">Permite enlazar la carpeta pública sin exponer el código</span>
                </div>
                <div>
                    <?php 
                    $symlinkAllowed = function_exists('symlink');
                    if ($symlinkAllowed): 
                    ?>
                        <span class="bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded text-xs font-bold">
                            Permitido
                        </span>
                    <?php else: ?>
                        <span class="bg-rose-600/20 text-rose-400 border border-rose-500/30 px-3 py-1 rounded text-xs font-bold">
                            Bloqueado - Consulta con Strato
                        </span>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="mt-8 pt-4 border-t border-slate-700 text-center text-xs text-slate-400">
            Elimina este archivo (<code>check_corrol.php</code>) de tu servidor una vez finalices la prueba.
        </div>
    </div>
</body>
</html>
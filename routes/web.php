<?php

use App\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\SheetController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ComicMetadataController;

// Route::get('/', function () {
//     return redirect()->route('resources.index');
// });

// Rutas de Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rutas para Recursos (CRUD completo)
Route::get('/resources', [ResourceController::class, 'index'])->name('resources.index');
Route::get('/resources/create', [ResourceController::class, 'create'])->name('resources.create');
Route::post('/resources', [ResourceController::class, 'store'])->name('resources.store');
Route::get('/resources/{id}', [ResourceController::class, 'show'])->name('resources.show');

// Edición y Eliminación (Solo usuarios autenticados)
Route::middleware('auth')->group(function () {
    Route::get('/resources/{id}/edit', [ResourceController::class, 'edit'])->name('resources.edit');
    Route::put('/resources/{id}', [ResourceController::class, 'update'])->name('resources.update');
    Route::delete('/resources/{id}', [ResourceController::class, 'destroy'])->name('resources.destroy');
});

// Listado de Juegos y Usuarios (Públicos)
Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
// Rutas para Campañas y Autores
Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');

// Edición de Perfil de Usuario (Solo autenticados)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Formulario público de Fichas/Diarios/Mapas con aviso para invitados
Route::middleware('auth')->group(function () {
    // Fichas
    Route::get('/sheets/create', [SheetController::class, 'createSheet'])->name('sheets.create');
    Route::post('/sheets', [SheetController::class, 'store'])->name('sheets.store');

    // Diarios
    Route::get('/diaries/create', [SheetController::class, 'createDiary'])->name('diaries.create');

    // Campañas
    Route::get('/campaigns/create', [SheetController::class, 'createCampaign'])->name('campaigns.create');

    // Mapas
    Route::get('/maps/create', [MapController::class, 'create'])->name('maps.create');
    Route::post('/maps', [MapController::class, 'store'])->name('maps.store');
});

// Rutas con redirección o aviso si intenta acceder siendo invitado
Route::get('/sheets/create-guest', function() {
    return view('errors.guest-restricted', ['type' => 'Fichas de PNJ']);
})->name('sheets.guest');

Route::get('/resources/{id}/stream', [ResourceController::class, 'streamPdf'])->name('resources.stream');

Route::get('/legal', function () {
    return view('legal');
})->name('legal');

Route::get('/favorites/{userId?}', [\App\Http\Controllers\FavoriteController::class, 'index'])->name('favorites.index');
Route::post('/resources/{resource}/favorite', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->middleware('auth')->name('resources.favorite.toggle');

/*
|--------------------------------------------------------------------------
| Ruta de Mantenimiento y Setup para Hosting Compartido (Strato sin SSH)
|--------------------------------------------------------------------------
*/
// Route::get('/strato-setup', function (\Illuminate\Http\Request $request) {
//     try {
//         $keyFromConfig = config('app.setup_secret_key');
//         $keyFromEnv = env('SETUP_SECRET_KEY');
//         $providedKey = $request->get('key');

//         $targetKey = $keyFromConfig ?: $keyFromEnv;

//         if (!$providedKey || !$targetKey || $providedKey !== $targetKey) {
//             return response("<pre>❌ 403 Acceso Denegado.\n\nLa clave introducida en la URL no coincide con la variable SETUP_SECRET_KEY de Strato.</pre>", 403);
//         }

//         $output = [];

//         // 0. Limpiar cachés antiguas
//         try {
//             \Illuminate\Support\Facades\Artisan::call('config:clear');
//             \Illuminate\Support\Facades\Artisan::call('route:clear');
//             \Illuminate\Support\Facades\Artisan::call('view:clear');
//             $output[] = "🧹 Cachés antiguas limpiadas.";
//         } catch (\Throwable $e) {
//             $output[] = "⚠️ Limpieza de caché: " . $e->getMessage();
//         }

//         // 1. Limpiar automáticamente las tablas antiguas 'cr_' antes de migrar
//         try {
//             \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
//             $tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE 'cr_%'");
//             foreach ($tables as $table) {
//                 $tableName = array_values((array)$table)[0];
//                 \Illuminate\Support\Facades\DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
//             }
//             \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
//             $output[] = "🗑️ Tablas antiguas 'cr_' eliminadas de la base de datos.";
//         } catch (\Throwable $e) {
//             $output[] = "⚠️ Limpieza de tablas SQL: " . $e->getMessage();
//         }

//         // 2. Ejecutar migraciones de base de datos desde cero
//         try {
//             \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
//             $output[] = "✅ Migraciones ejecutadas limpias:\n" . \Illuminate\Support\Facades\Artisan::output();
//         } catch (\Throwable $e) {
//             $output[] = "❌ Error en Migraciones: " . $e->getMessage();
//         }

//         // 3. Crear enlace simbólico de storage
//         try {
//             \Illuminate\Support\Facades\Artisan::call('storage:link');
//             $output[] = "✅ Enlace simbólico de Storage generado.";
//         } catch (\Throwable $e) {
//             $output[] = "⚠️ Symlink Storage: " . $e->getMessage();
//         }

//         // 4. Generar cachés de producción
//         try {
//             \Illuminate\Support\Facades\Artisan::call('config:cache');
//             \Illuminate\Support\Facades\Artisan::call('route:cache');
//             \Illuminate\Support\Facades\Artisan::call('view:cache');
//             $output[] = "🚀 Cachés de producción optimizadas con éxito.";
//         } catch (\Throwable $e) {
//             $output[] = "⚠️ Caché de producción: " . $e->getMessage();
//         }

//         return response('<pre>' . implode("\n\n", $output) . '</pre>');

//     } catch (\Throwable $e) {
//         return response('<pre>❌ Error grave 500:\n\n' . $e->getMessage() . '</pre>', 500);
//     }
// });

// 🔑 Ruta de Activación Secreta de Admin usando SETUP_SECRET_KEY (Solo accesible si estás logueado)
Route::get('/secret-claim-admin/{secretKey}', [\App\Http\Controllers\Admin\AdminController::class, 'claimAdmin'])
    ->middleware('auth')
    ->name('admin.claim');

// 🔒 Grupo de Rutas de Administración (Devuelve 404 a cualquier usuario normal)
Route::middleware(['auth', 'admin'])->prefix('secret-admin-panel')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/resources/{id}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'editResource'])->name('admin.resources.edit');
    Route::put('/resources/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'updateResource'])->name('admin.resources.update');
    Route::get('/mailing', [\App\Http\Controllers\Admin\AdminController::class, 'mailingForm'])->name('admin.mailing');
    Route::post('/mailing/send', [\App\Http\Controllers\Admin\AdminController::class, 'sendMailing'])->name('admin.mailing.send');
});

// En lugar de redireccionar con redirect():
Route::get('/', [ResourceController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/resources/{resource}/comic-stream', [ComicMetadataController::class, 'streamComic'])->name('comic.stream');
    Route::get('/resources/{resource}/comic-mapper', [ComicMetadataController::class, 'edit'])->name('comic.mapper.edit');
    Route::put('/resources/{resource}/comic-metadata', [ComicMetadataController::class, 'update'])->name('comic.metadata.update');
    Route::delete('/resources/{resource}/comic-metadata', [ComicMetadataController::class, 'destroy'])->name('comic.metadata.destroy');
});

Route::get('/resources/{resource}/proxy-stream', function (\App\Models\Resource $resource) {
    if ($resource->privacy === 'private' && $resource->user_id !== auth()->id()) {
        abort(403);
    }
    $resourceFile = $resource->resourceable;
    if (!$resourceFile) {
        abort(404);
    }
    if ($resourceFile->is_external) {
        $response = \Illuminate\Support\Facades\Http::withoutVerifying()->get($resourceFile->file_path_or_url);
        if (!$response->successful()) {
            abort(404, 'No se pudo obtener el archivo externo.');
        }
        return response($response->body(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);
    }
    return app(\App\Services\ResourceUploadService::class)->streamFile($resource);
})->name('resources.proxy-stream');
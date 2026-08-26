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

Route::get('/', function () {
    return redirect()->route('resources.index');
});

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
Route::get('/strato-setup', function (\Illuminate\Http\Request $request) {
    $secretKey = env('SETUP_SECRET_KEY', 'MiClaveDeSeguridad123!');

    if ($request->get('key') !== $secretKey) {
        abort(403, 'Acceso denegado: Clave de seguridad incorrecta.');
    }

    $output = [];

    // 1. Ejecutar migraciones de base de datos
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $output[] = "✅ Migraciones ejecuadas: " . \Illuminate\Support\Facades\Artisan::output();

    // 2. Crear enlace simbólico público de storage
    try {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        $output[] = "✅ Enlace simbólico de Storage generado.";
    } catch (\Exception $e) {
        $output[] = "⚠️ Symlink: " . $e->getMessage();
    }

    // 3. Generar cachés de producción con las rutas reales del servidor de Strato
    \Illuminate\Support\Facades\Artisan::call('config:cache');
    \Illuminate\Support\Facades\Artisan::call('route:cache');
    \Illuminate\Support\Facades\Artisan::call('view:cache');
    $output[] = "🚀 Cachés de producción generadas en Strato.";

    return response('<pre>' . implode("\n", $output) . '</pre>');
});

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

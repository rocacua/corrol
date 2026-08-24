<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\User;
use App\Services\FavoriteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    protected FavoriteService $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    public function toggle(Resource $resource): RedirectResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }

        $isFavorited = $this->favoriteService->toggleFavorite($userId, $resource->id);

        $message = $isFavorited
            ? '¡Recurso añadido a tus favoritos!'
            : 'Recurso eliminado de tus favoritos.';

        return redirect()->back()->with('success', $message);
    }

    public function index(Request $request, ?int $userId = null): View
    {
        $targetUserId = $userId ?? Auth::id();

        if (!$targetUserId) {
            abort(404, 'Usuario no especificado.');
        }

        $targetUser = User::findOrFail($targetUserId);
        $filters = $request->only(['sort']);
        $viewerUserId = Auth::id();

        $resources = $this->favoriteService->getFavoritesForUser($targetUserId, $viewerUserId, $filters);

        return view('favorites.index', compact('resources', 'targetUser', 'filters'));
    }
}
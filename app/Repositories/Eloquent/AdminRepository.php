<?php

namespace App\Repositories\Eloquent;

use App\Models\Resource;
use App\Models\ResourceFile;
use App\Models\User;
use App\Repositories\Contracts\AdminRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminRepository implements AdminRepositoryInterface
{
    public function getGlobalStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_resources' => Resource::count(),
            'public_resources' => Resource::where('privacy', 'public')->count(),
            'private_resources' => Resource::where('privacy', 'private')->count(),
            'total_storage_bytes' => (int) ResourceFile::where('is_external', false)->sum('size_in_bytes'),
            'resources_by_type' => Resource::query()
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }

    public function updateResourceOwner(Resource $resource, ?int $newUserId): Resource
    {
        $resource->update(['user_id' => $newUserId]);
        return $resource;
    }

    public function getAllUsers(): Collection
    {
        return User::orderBy('name')->get();
    }

    public function filterUsersForMailing(array $filters): Collection
    {
        return User::query()
            ->when(!empty($filters['game']), function (Builder $q) use ($filters) {
                // Usuarios que han creado O guardado en favoritos recursos de este juego
                $game = $filters['game'];
                $q->where(function (Builder $sub) use ($game) {
                    $sub->whereHas('resources', fn($r) => $r->where('game', 'like', "%{$game}%"))
                        ->orWhereHas('favorites', fn($f) => $f->where('game', 'like', "%{$game}%"));
                });
            })
            ->when(!empty($filters['tag']), function (Builder $q) use ($filters) {
                // Usuarios con recursos o favoritos que incluyan esta etiqueta
                $tag = $filters['tag'];
                $q->where(function (Builder $sub) use ($tag) {
                    $sub->whereHas('resources', fn($r) => $r->whereJsonContains('tags', $tag))
                        ->orWhereHas('favorites', fn($f) => $f->whereJsonContains('tags', $tag));
                });
            })
            ->when(!empty($filters['has_resource_type']), function (Builder $q) use ($filters) {
                $type = $filters['has_resource_type'];
                $q->whereHas('resources', fn($r) => $r->where('type', $type));
            })
            ->distinct()
            ->get();
    }
}
<?php

namespace App\Repositories\Eloquent;

use App\Models\Resource;
use App\Models\ResourceFile;
use App\Models\ResourceMap;
use App\Models\ResourceSheet;
use App\Models\User;
use App\Repositories\Contracts\ResourceRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResourceRepository implements ResourceRepositoryInterface
{
    /**
     * Crea un registro de tipo archivo o enlace guardando ambas tablas en una transacción.
     */
    public function createFileResource(array $data, string $filePathOrUrl, array $metadata): Resource
    {
        return DB::transaction(function () use ($data, $filePathOrUrl, $metadata) {
            /** @var ResourceFile $resourceFile */
            $resourceFile = ResourceFile::create([
                'file_path_or_url' => $filePathOrUrl,
                'is_external' => $data['is_external'] ?? false,
                'file_type' => $metadata['file_type'] ?? ($data['file_type'] ?? 'document'),
                'mime_type' => $metadata['mime_type'] ?? null,
                'size_in_bytes' => $data['size_in_bytes'] ?? null,
                'metadata' => $metadata,
            ]);

            /** @var Resource $resource */
            $resource = $resourceFile->resource()->create([
                'user_id' => $data['user_id'] ?? Auth::id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'privacy' => $data['privacy'] ?? 'public',
                'type' => 'file',
                'game' => $data['game'] ?? null,
                'campaign' => $data['campaign'] ?? null,
                'author' => $data['author'] ?? null,
                'tags' => $data['tags'] ?? [],
            ]);

            return $resource;
        });
    }

    public function getUniqueValues(string $field): array
    {
        return Resource::whereNotNull($field)
            ->where($field, '!=', '')
            ->distinct()
            ->pluck($field)
            ->toArray();
    }

    public function getTotalUsedStorageInBytes(): int
    {
        return (int) ResourceFile::where('is_external', false)->sum('size_in_bytes');
    }

    public function getUniqueTags(): array
    {
        return Resource::whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->map(fn ($tag) => trim((string) $tag))
            ->unique()
            ->filter()
            ->values()
            ->toArray();
    }

     public function findById(int $id): ?Resource
    {
        /** @var Resource|null */
        return Resource::with('resourceable')->find($id);
    }

    public function searchResources(array $filters): mixed
    {
        $userId = Auth::id();

        $query = Resource::query()
            ->with(['resourceable', 'user'])
            ->withCount('favoritedBy')
            ->where(function (Builder $query) use ($userId) {
                $query->where('privacy', 'public');
                if ($userId) {
                    $query->orWhere('user_id', $userId);
                }
            })
            // Término de búsqueda general (Título, Descripción, Juego, Campaña, Autor, Contenido JSON de bloques)
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $term = trim((string) $filters['q']);
                $query->where(function (Builder $sub) use ($term) {
                    $sub->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('game', 'like', "%{$term}%")
                        ->orWhere('campaign', 'like', "%{$term}%")
                        ->orWhere('author', 'like', "%{$term}%")
                        ->orWhere('tags', 'like', "%{$term}%")
                        ->orWhere('type', 'like', "%{$term}%")
                        // Búsqueda en contenido de fichas/diarios/campañas (JSON)
                        ->orWhereHasMorph('resourceable', [\App\Models\ResourceSheet::class], function (Builder $q) use ($term) {
                            $q->where('content', 'like', "%{$term}%");
                    });
                });
            })
            // Filtros específicos
            ->when(!empty($filters['game']), function (Builder $query) use ($filters) {
                $query->where('game', 'like', "%" . trim((string) $filters['game']) . "%");
            })
            ->when(!empty($filters['campaign']), function (Builder $query) use ($filters) {
                $query->where('campaign', 'like', "%" . trim((string) $filters['campaign']) . "%");
            })
            ->when(!empty($filters['author']), function (Builder $query) use ($filters) {
                $query->where('author', 'like', "%" . trim((string) $filters['author']) . "%");
            })
            ->when(!empty($filters['type']), function (Builder $query) use ($filters) {
                $query->where('type', $filters['type']);
            })
            ->when(!empty($filters['tag']), function (Builder $query) use ($filters) {
                $query->whereJsonContains('tags', trim((string) $filters['tag']));
            })
            ->when(!empty($filters['user_id']), function (Builder $query) use ($filters) {
                $query->where('user_id', $filters['user_id']);
            })
            // Filtro por Rangos de Fecha de Publicación
            ->when(!empty($filters['date_from']), function (Builder $query) use ($filters) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            })
            ->when(!empty($filters['date_to']), function (Builder $query) use ($filters) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            });

         // 1. Calculamos el total de favoritos globales que cumplen con los filtros usando una subconsulta
        // Contamos cuántos registros de la consulta filtrada tienen al menos 1 favorito
        $totalFavorited = (clone $query)->whereHas('favoritedBy')->count();
        // Ordenación dinámica
        $sort = $filters['sort'] ?? 'latest';
        
        if ($sort === 'affinity' && $userId) {
            $favSubquery = DB::table('favorite_resources')
                ->selectRaw('count(*)')
                ->whereColumn('favorite_resources.resource_id', 'resources.id')
                ->where('favorite_resources.user_id', (int) $userId);

            $currentUserId = (int) $userId;

            $query->orderByDesc($favSubquery)
                  ->orderByRaw("CASE WHEN user_id = {$currentUserId} THEN 1 ELSE 0 END DESC")
                  ->orderByRaw("CASE WHEN privacy = 'private' THEN 1 ELSE 0 END DESC")
                  ->latest();
        } else {
            match ($sort) {
                'oldest' => $query->oldest(),
                'title_asc' => $query->orderBy('title', 'asc'),
                'title_desc' => $query->orderBy('title', 'desc'),
                'type' => $query->orderBy('type', 'asc')->latest(),
                default => $query->latest(),
            };
        }

        // 2. Ejecutamos la paginación
        $paginator = $query->paginate(12)->withQueryString();
        // 3. Inyectamos la propiedad dinámica al paginador para usarla en la vista
        $paginator->total_favorited = $totalFavorited;
        return $paginator;
    }

    public function updateResource(Resource $resource, array $data): Resource
    {
        $updateData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'privacy' => $data['privacy'] ?? $resource->privacy,
            'game' => $data['game'] ?? null,
            'campaign' => $data['campaign'] ?? null,
            'author' => $data['author'] ?? null,
            'tags' => $data['tags'] ?? [],
        ];

        if ($resource->user_id === null && Auth::check()) {
            $updateData['user_id'] = Auth::id();
        }

        $resource->update($updateData);

        return $resource;
    }
    
    public function findByUrl(string $url): ?Resource
    {
        /** @var ResourceFile|null $file */
        $file = ResourceFile::where('file_path_or_url', $url)->first();
        return $file ? $file->resource : null;
    }

    public function createSheetResource(array $data, array $content): Resource
    {
        return DB::transaction(function () use ($data, $content) {
            /** @var User|null $user */
            $user = Auth::user();
            $authorName = $user ? $user->name : 'Anónimo';

            /** @var ResourceSheet $sheet */
            $sheet = ResourceSheet::create(['content' => $content]);

            /** @var Resource $resource */
            $resource = $sheet->resource()->create([
                'user_id' => Auth::id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'privacy' => $data['privacy'] ?? 'public',
                'type' => $data['type'],
                'game' => $data['game'] ?? null,
                'campaign' => $data['campaign'] ?? null,
                'author' => $data['author'] ?? $authorName,
                'tags' => $data['tags'] ?? [],
            ]);

            return $resource;
        });
    }

    public function updateSheetResource(Resource $resource, array $data, array $content): Resource
    {
        return DB::transaction(function () use ($resource, $data, $content) {
            if ($resource->resourceable) {
                $resource->resourceable->update(['content' => $content]);
            }

            $resource->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'privacy' => $data['privacy'] ?? $resource->privacy,
                'game' => $data['game'] ?? null,
                'campaign' => $data['campaign'] ?? null,
                'author' => $data['author'] ?? $resource->author,
                'tags' => $data['tags'] ?? [],
            ]);

            return $resource;
        });
    }

    public function createMapResource(array $data, string $mapImageUrl, array $markers): Resource
    {
        return DB::transaction(function () use ($data, $mapImageUrl, $markers) {
            /** @var User|null $user */
            $user = Auth::user();
            $authorName = $user ? $user->name : 'Anónimo';

            /** @var ResourceMap $map */
            $map = ResourceMap::create([
                'map_image_url' => $mapImageUrl,
                'markers' => $markers,
            ]);

            /** @var Resource $resource */
            $resource = $map->resource()->create([
                'user_id' => Auth::id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'privacy' => $data['privacy'] ?? 'public',
                'type' => 'map',
                'game' => $data['game'] ?? null,
                'campaign' => $data['campaign'] ?? null,
                'author' => $data['author'] ?? $authorName,
                'tags' => $data['tags'] ?? [],
            ]);

            return $resource;
        });
    }

    public function updateMapResource(Resource $resource, array $data, string $mapImageUrl, array $markers): Resource
    {
        return DB::transaction(function () use ($resource, $data, $mapImageUrl, $markers) {
            if ($resource->resourceable) {
                $resource->resourceable->update([
                    'map_image_url' => $mapImageUrl,
                    'markers' => $markers,
                ]);
            }

            $resource->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'privacy' => $data['privacy'] ?? $resource->privacy,
                'game' => $data['game'] ?? null,
                'campaign' => $data['campaign'] ?? null,
                'author' => $data['author'] ?? $resource->author,
                'tags' => $data['tags'] ?? [],
            ]);

            return $resource;
        });
    }

    public function getUserResourcesByType(int $userId, string $type): mixed
    {
        return Resource::where('user_id', $userId)
            ->where('type', $type)
            ->latest()
            ->get();
    }

    public function toggleFavorite(int $userId, int $resourceId): bool
    {
        /** @var User|null $user */
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $changes = $user->favorites()->toggle($resourceId);
        return count($changes['attached']) > 0;
    }

    public function isFavorited(int $userId, int $resourceId): bool
    {
        /** @var User|null $user */
        $user = User::find($userId);
        return $user ? $user->favorites()->where('resource_id', $resourceId)->exists() : false;
    }

    public function getFavoriteResources(int $targetUserId, ?int $viewerUserId = null, array $filters = []): mixed
    {
        $sort = $filters['sort'] ?? 'latest';

        $query = Resource::query()
            ->whereHas('favoritedBy', function (Builder $q) use ($targetUserId) {
                $q->where('user_id', $targetUserId);
            })
            ->with(['resourceable', 'user'])
            ->withCount('favoritedBy')
            ->where(function (Builder $query) use ($viewerUserId) {
                $query->where('privacy', 'public');
                if ($viewerUserId) {
                    $query->orWhere('user_id', $viewerUserId);
                }
            });

        match ($sort) {
            'oldest' => $query->oldest(),
            'title_asc' => $query->orderBy('title', 'asc'),
            'title_desc' => $query->orderBy('title', 'desc'),
            default => $query->latest(),
        };

        return $query->paginate(12)->withQueryString();
    }
}

<?php

namespace App\Services;

use App\Repositories\Contracts\ResourceRepositoryInterface;

class FavoriteService
{
    protected ResourceRepositoryInterface $resourceRepository;

    public function __construct(ResourceRepositoryInterface $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    public function toggleFavorite(int $userId, int $resourceId): bool
    {
        return $this->resourceRepository->toggleFavorite($userId, $resourceId);
    }

    public function isFavorited(int $userId, int $resourceId): bool
    {
        return $this->resourceRepository->isFavorited($userId, $resourceId);
    }

    public function getFavoritesForUser(int $targetUserId, ?int $viewerUserId = null, array $filters = []): mixed
    {
        return $this->resourceRepository->getFavoriteResources($targetUserId, $viewerUserId, $filters);
    }
}
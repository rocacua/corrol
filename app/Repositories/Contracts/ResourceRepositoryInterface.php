<?php

namespace App\Repositories\Contracts;

use App\Models\Resource;

interface ResourceRepositoryInterface
{
    public function createFileResource(array $data, string $filePathOrUrl, array $metadata): mixed;
    public function getUniqueValues(string $field): array;
    public function getUniqueTags(): array;
    public function getTotalUsedStorageInBytes(): int;
    public function findById(int $id): mixed;
    public function findByUrl(string $url): mixed;
    public function searchResources(array $filters): mixed;
    public function updateResource(Resource $resource, array $data): Resource;
    public function createSheetResource(array $data, array $content): mixed;
    public function updateSheetResource(Resource $resource, array $data, array $content): mixed;
    public function createMapResource(array $data, string $mapImageUrl, array $markers): mixed;
    public function updateMapResource(Resource $resource, array $data, string $mapImageUrl, array $markers): mixed;
    public function getUserResourcesByType(int $userId, string $type): mixed;
    public function toggleFavorite(int $userId, int $resourceId): bool;
    public function isFavorited(int $userId, int $resourceId): bool;
    public function getFavoriteResources(int $targetUserId, ?int $viewerUserId = null, array $filters = []): mixed;
    public function updateComicMetadata(int $id, ?array $metadata): bool;
}
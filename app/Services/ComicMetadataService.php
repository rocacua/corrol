<?php
namespace App\Services;

use App\Repositories\Contracts\ResourceRepositoryInterface;

class ComicMetadataService
{
    public function __construct(
        private ResourceRepositoryInterface $resourceRepository
    ) {}

    public function saveMetadata(int $resourceId, array $data): bool
    {
        // Aquí podrías validar la estructura exacta del array $data
        return $this->resourceRepository->updateComicMetadata($resourceId, $data);
    }

    public function removeMetadata(int $resourceId): bool
    {
        return $this->resourceRepository->updateComicMetadata($resourceId, null);
    }
}
<?php

namespace App\Services;

use App\Models\Resource;
use App\Repositories\Contracts\AdminRepositoryInterface;
use App\Repositories\Contracts\ResourceRepositoryInterface;
use Illuminate\Support\Collection;

class AdminService
{
    protected AdminRepositoryInterface $adminRepository;
    protected ResourceRepositoryInterface $resourceRepository;

    public function __construct(
        AdminRepositoryInterface $adminRepository,
        ResourceRepositoryInterface $resourceRepository
    ) {
        $this->adminRepository = $adminRepository;
        $this->resourceRepository = $resourceRepository;
    }

    public function getDashboardStats(): array
    {
        return $this->adminRepository->getGlobalStats();
    }

    public function reassignResourceOwner(int $resourceId, ?int $newUserId, array $data): Resource
    {
        /** @var Resource $resource */
        $resource = Resource::findOrFail($resourceId);

        // Actualización de metadatos básicos
        $this->resourceRepository->updateResource($resource, $data);

        // Cambio de propietario sin restricciones de permisos
        return $this->adminRepository->updateResourceOwner($resource, $newUserId);
    }

    public function getUsersForMailing(array $filters): Collection
    {
        return $this->adminRepository->filterUsersForMailing($filters);
    }

    public function getAllUsers(): Collection
    {
        return $this->adminRepository->getAllUsers();
    }
}
<?php

namespace App\Repositories\Contracts;

use App\Models\Resource;
use Illuminate\Support\Collection;

interface AdminRepositoryInterface
{
    public function getGlobalStats(): array;
    public function updateResourceOwner(Resource $resource, ?int $newUserId): Resource;
    public function filterUsersForMailing(array $filters): Collection;
    public function getAllUsers(): Collection;
}
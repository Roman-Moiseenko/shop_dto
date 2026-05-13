<?php

namespace App\Modules\Auth\Domain\Services;
use Spatie\Permission\Models\Role;
interface RoleRepositoryInterface
{
    public function create(array $data): Role;
    public function update(int $id, array $data): Role;
    public function delete(int $id): void;
    public function findById(int $id): ?Role;
}

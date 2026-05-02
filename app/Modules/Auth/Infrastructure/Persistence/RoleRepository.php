<?php

namespace App\Modules\Auth\Infrastructure\Persistence;

use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use Spatie\Permission\Models\Role;
class RoleRepository implements RoleRepositoryInterface
{
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(int $id, array $data): Role
    {
        $role = Role::findOrFail($id);
        $role->update($data);
        return $role;
    }

    public function delete(int $id): void
    {
        Role::findOrFail($id)->delete();
    }

    public function findById(int $id): ?Role
    {
        return Role::find($id);
    }
}

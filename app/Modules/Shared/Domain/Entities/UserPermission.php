<?php

namespace App\Modules\Shared\Domain\Entities;

/**
 * Сущность доступа и роли
 */
class UserPermission
{
    private $userId;
    private array $roles;
    private array $permissions {
        get {
            return $this->permissions;
        }
    }

    public function __construct(int $userId, array $roles, array $permissions)
    {
        $this->userId = $userId;
        $this->roles = $roles;
        $this->permissions = $permissions;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
         return in_array($role, $this->roles);
    }
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }
}

<?php

namespace App\Modules\Shared\Domain\Entities;

/**
 * Сущность доступа и роли
 */
class UserPermission
{
    private ?int $userId {
        get => $this->userId;
    }
    private array $roles {
        get => $this->roles;
    }
    private array $permissions {
        get => $this->permissions;
    }

    public function __construct(?int $userId, array $roles, array $permissions)
    {
        $this->userId = $userId;
        $this->roles = $roles;
        $this->permissions = $permissions;
    }

    public function getId(): ?int
    {
        return $this->userId;
    }
    public function hasRole(string $role): bool
    {
         return in_array($role, $this->roles);
    }
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    /**
     * Синоним hasPermission
     */
    public function can(string $permission): bool
    {
        return $this->hasPermission($permission);
    }
}

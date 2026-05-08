<?php

namespace Tests\Trait;

use App\Modules\Shared\Domain\Entities\UserPermission;
use Mockery;

trait MockPermission
{
    abstract function getModuleName(): string;
    abstract function getEntityName(): string;

    protected function mockUserPermission(
        bool $view = false,
        bool $create = false,
        bool $edit = false,
        bool $delete = false,
        bool $force = false,
        bool $blocked = false,
        ?int $id = null,
        bool $role = true,
    ): UserPermission
    {
        $prefix = $this->getModuleName() . '.' . $this->getEntityName() . '.';
        $permission = Mockery::mock(UserPermission::class);
        $permission->shouldReceive('can')
            ->andReturnUsing(fn($permission) => match ($permission) {
                $prefix . 'view' => $view,
                $prefix . 'edit' => $edit,
                $prefix . 'create' => $create,
                $prefix . 'delete' => $delete,
                $prefix . 'force' => $force,
                $prefix . 'blocked' => $blocked,
                default => false,
            });
        $permission->shouldReceive('getId')->andReturn($id);
        $permission->shouldReceive('hasRole')->andReturn($role);
        return $permission;
    }
}

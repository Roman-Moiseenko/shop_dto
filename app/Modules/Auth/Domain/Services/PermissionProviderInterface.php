<?php

namespace App\Modules\Auth\Domain\Services;

interface PermissionProviderInterface
{
    public function groupedBySystemRoles(): array;
}

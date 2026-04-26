<?php

namespace App\Modules\Shared\Application\Interfaces;

use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\Request;

interface UserPermissionRepositoryInterface
{
    public function getUserPermission(Request $request): UserPermission;
}

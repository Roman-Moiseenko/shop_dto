<?php

namespace App\Modules\Shared\Presentation\Http\Middlewares;

use App\Modules\Shared\Application\Interfaces\UserPermissionRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Closure;
use Illuminate\Http\Request;

readonly class LoadUserPermission
{
    public function __construct(
        private UserPermissionRepositoryInterface $permissionRepo
    ) {}
    public function handle(Request $request, Closure $next)
    {
        // Получаем объект прав текущего пользователя
        $userPermission = $this->permissionRepo->getUserPermission($request);

        // Регистрируем его в контейнере, чтобы методы контроллера могли получить через DI
        app()->instance(UserPermission::class, $userPermission);

        return $next($request);
    }
}

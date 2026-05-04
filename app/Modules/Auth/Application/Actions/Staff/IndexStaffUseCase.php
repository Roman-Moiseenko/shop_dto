<?php

namespace App\Modules\Auth\Application\Actions\Staff;

use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Infrastructure\Models\Staff;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
readonly class IndexStaffUseCase
{
    public function __construct(
        private StaffRepositoryInterface $staffRepository
    ) {}

    public function execute(UserPermission $permissions, int $perPage = 15): LengthAwarePaginator
    {
        if (!$permissions->can('auth.employee.view')) throw new AccessDeniedException();

        //Добавить Фильтры и использовать $staffRepository
        return Staff::with('user')->paginate();

    }
}

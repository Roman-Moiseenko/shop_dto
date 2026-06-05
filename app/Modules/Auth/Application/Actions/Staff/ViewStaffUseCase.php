<?php

namespace App\Modules\Auth\Application\Actions\Staff;

use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Domain\Entities\StaffEntity;
use App\Modules\Auth\Domain\Exceptions\StaffNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

readonly class ViewStaffUseCase
{
    public function __construct(
        private StaffRepositoryInterface $staffRepository
    ) {}

    public function execute(int $staffId, UserPermission $permissions): StaffEntity
    {
        if (!$permissions->can('auth.employee.view')) throw new AccessDeniedException();

        $staff = $this->staffRepository->findById($staffId);
        if (!$staff) {
            throw new StaffNotFoundException('Сотрудник не найден');
        }
        return $staff;
    }
}

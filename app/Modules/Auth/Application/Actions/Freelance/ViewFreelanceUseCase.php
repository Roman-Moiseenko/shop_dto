<?php

namespace App\Modules\Auth\Application\Actions\Freelance;

use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use App\Modules\Auth\Domain\Exceptions\StaffNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

readonly class ViewFreelanceUseCase
{
    public function __construct(
        private FreelanceRepositoryInterface $freelanceRepository
    ) {}

    public function execute(int $freelanceId, UserPermission $permissions): FreelanceEntity
    {
        if (!$permissions->can('auth.employee.view')) throw new AccessDeniedException();

        $freelance = $this->freelanceRepository->findById($freelanceId);
        if (!$freelance) {
            throw new StaffNotFoundException('Сотрудник не найден');
        }
        return $freelance;
    }
}

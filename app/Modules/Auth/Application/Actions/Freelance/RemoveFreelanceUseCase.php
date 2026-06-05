<?php

namespace App\Modules\Auth\Application\Actions\Freelance;

use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

class RemoveFreelanceUseCase
{
    public function __construct(
        private readonly FreelanceRepositoryInterface $freelanceRepository
    )
    {
    }
    public function execute(int $id, UserPermission $permissions): bool
    {
        if (!$permissions->can('auth.employee.delete')) throw new AccessDeniedException();
        //Проверка, можем ли удалить

        return $this->freelanceRepository->delete($id);
    }
}

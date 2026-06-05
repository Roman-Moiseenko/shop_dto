<?php

namespace App\Modules\Auth\Application\Actions\Freelance;

use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class IndexFreelanceUseCase
{

    public function __construct(private FreelanceRepositoryInterface $freelanceRepository)
    {
    }

    public function execute(UserPermission $permissions, int $perPage = 15): LengthAwarePaginator
    {
        if (!$permissions->can('auth.employee.view')) throw new AccessDeniedException();

        //Добавить Фильтры и использовать $freelanceRepository
        return $this->freelanceRepository->paginate($perPage);
    }
}

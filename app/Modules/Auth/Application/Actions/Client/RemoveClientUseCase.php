<?php

namespace App\Modules\Auth\Application\Actions\Client;

use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Infrastructure\Models\Staff;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

class RemoveClientUseCase
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository
    )
    {
    }
    public function execute(int $id, UserPermission $permissions): bool
    {
        if (!$permissions->can('auth.buyer.delete')) throw new AccessDeniedException();

        //Проверка, можем ли удалить

        return $this->clientRepository->delete($id);
    }
}

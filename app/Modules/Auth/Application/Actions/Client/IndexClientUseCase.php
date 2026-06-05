<?php

namespace App\Modules\Auth\Application\Actions\Client;

use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class IndexClientUseCase
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository
    ) {}

    public function execute(UserPermission $permissions, int $perPage = 15): LengthAwarePaginator
    {
        if (!$permissions->can('auth.buyer.view')) throw new AccessDeniedException();

        //Добавить Фильтры и использовать $clientRepository
        return $this->clientRepository->paginate($perPage);

    }
}

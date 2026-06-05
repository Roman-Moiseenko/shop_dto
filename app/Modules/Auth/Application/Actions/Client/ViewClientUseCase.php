<?php

namespace App\Modules\Auth\Application\Actions\Client;

use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\Exceptions\ClientNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

readonly class ViewClientUseCase
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository
    ) {}

    public function execute(int $clientId, UserPermission $permissions): ClientEntity
    {
        if (!$permissions->can('auth.buyer.view')) throw new AccessDeniedException();

        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new ClientNotFoundException('Клиент не найден');
        }
        return $client;
    }
}

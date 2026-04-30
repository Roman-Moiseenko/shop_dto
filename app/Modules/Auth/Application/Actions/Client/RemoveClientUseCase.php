<?php

namespace App\Modules\Auth\Application\Actions\Client;

use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Infrastructure\Models\Staff;

class RemoveClientUseCase
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository
    )
    {
    }
    public function execute(int $id): bool
    {
        //TODO Проверка, можем ли удалить

        return $this->clientRepository->delete($id);
    }
}

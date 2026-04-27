<?php

namespace App\Modules\Auth\Application\Actions\Freelance;

use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;

class RemoveFreelanceUseCase
{
    public function __construct(
        private readonly FreelanceRepositoryInterface $freelanceRepository
    )
    {
    }
    public function execute(int $id): bool
    {
        //TODO Проверка, можем ли удалить

        return $this->freelanceRepository->delete($id);
    }
}

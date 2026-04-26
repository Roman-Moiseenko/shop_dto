<?php

namespace App\Modules\Auth\Application\Actions\Staff;

use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Infrastructure\Models\Staff;

class RemoveStaffUseCase
{
    public function __construct(
        private readonly StaffRepositoryInterface $staffRepository
    )
    {
    }
    public function execute(int $id): bool
    {
        //TODO Проверка, можем ли удалить

        return $this->staffRepository->delete($id);
    }
}

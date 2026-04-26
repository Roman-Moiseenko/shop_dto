<?php

namespace App\Modules\Auth\Application\Interfaces;

use App\Modules\Auth\Domain\Entities\StaffEntity;

interface StaffRepositoryInterface
{
    public function save(StaffEntity $staff): StaffEntity;
    public function findById(int $id): ?StaffEntity;
    public function findByUserId(int $userId): ?StaffEntity;
    public function delete(int $id): bool;
}

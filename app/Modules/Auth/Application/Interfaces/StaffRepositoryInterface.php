<?php

namespace App\Modules\Auth\Application\Interfaces;

use App\Modules\Auth\Domain\Entities\StaffEntity;
use Illuminate\Pagination\LengthAwarePaginator;

interface StaffRepositoryInterface
{
    public function save(StaffEntity $staff): StaffEntity;
    public function findById(int $id): ?StaffEntity;
    public function findByUserId(int $userId): ?StaffEntity;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}

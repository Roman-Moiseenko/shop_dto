<?php

namespace App\Modules\Auth\Application\Interfaces;

use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use Illuminate\Pagination\LengthAwarePaginator;

interface FreelanceRepositoryInterface
{
    public function save(FreelanceEntity $freelanceEntity): FreelanceEntity;
    public function findById(int $id): ?FreelanceEntity;
    public function findByUserId(int $userId): ?FreelanceEntity;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}

<?php

namespace App\Modules\Auth\Application\Interfaces;

use App\Modules\Auth\Domain\Entities\FreelanceEntity;

interface FreelanceRepositoryInterface
{
    public function save(FreelanceEntity $freelanceEntity): FreelanceEntity;
    public function findById(int $id): ?FreelanceEntity;
    public function findByUserId(int $userId): ?FreelanceEntity;
    public function delete(int $id): bool;
}

<?php

namespace App\Modules\Auth\Application\Interfaces;

use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use Illuminate\Http\Request;

interface UserRepositoryInterface
{
    public function save(UserEntity $user): UserEntity;
    public function findByEmail(Email $email): ?UserEntity;
    public function findById(int $id): ?UserEntity;
    public function emailExists(Email $email, ?int $excludeId = null): bool;

    public function fromRequest(Request $request): ?UserEntity;
    public function findByStaffId(int $id): ?UserEntity;
}

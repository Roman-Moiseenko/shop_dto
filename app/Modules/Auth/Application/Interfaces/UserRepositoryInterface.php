<?php

namespace App\Modules\Auth\Application\Interfaces;

use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function save(UserEntity $user): UserEntity;
    public function findByEmail(Email $email): ?UserEntity;
    public function findById(int $id): ?UserEntity;
    public function emailExists(Email $email, ?int $excludeId = null): bool;

    public function fromRequest(Request $request): ?UserEntity;
    public function findByStaffId(int $id): ?UserEntity;

    public function saveEmailVerification(int $userId, Email $newEmail, string $token, ?\DateTimeImmutable $expiresAt = null): void;
    public function findEmailVerificationByToken(string $token): ?object; // возвращает DTO/stdClass с полями user_id, new_email, expires_at
    public function deleteEmailVerification(string $token): void;
    public function paginate(int $perPage = 15): LengthAwarePaginator;

}

<?php

namespace App\Modules\Auth\Application\Interfaces;


use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use Illuminate\Pagination\LengthAwarePaginator;

interface ClientRepositoryInterface
{
    public function save(ClientEntity $client): ClientEntity;
    public function findById(int $id): ?ClientEntity;
    public function findByPhone(PhoneNumber $phone): ?ClientEntity;
    public function findByUserId(int $userId): ?ClientEntity;
    public function emailExists(Email $email, ?int $excludeId = null): bool;
    public function phoneExists(PhoneNumber $phone, ?int $excludeId = null): bool;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}

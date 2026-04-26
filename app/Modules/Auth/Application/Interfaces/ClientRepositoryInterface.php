<?php

namespace App\Modules\Auth\Application\Interfaces;


use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;

interface ClientRepositoryInterface
{
    public function save(ClientEntity $client): ClientEntity;
    public function findById(int $id): ?ClientEntity;
    public function findByPhone(PhoneNumber $phone): ?ClientEntity;
    public function findByUserId(int $userId): ?ClientEntity;
    public function delete(int $id): bool;
}

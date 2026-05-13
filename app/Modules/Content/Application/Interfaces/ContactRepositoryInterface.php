<?php

namespace App\Modules\Content\Application\Interfaces;

use App\Modules\Content\Domain\Entities\ContactEntity;

interface ContactRepositoryInterface
{
    public function save(ContactEntity $contact): ContactEntity;
    public function findById(int $id): ?ContactEntity;
    public function delete(int $id): void;
    public function findAllActive(): array;
    public function all(): array;
}

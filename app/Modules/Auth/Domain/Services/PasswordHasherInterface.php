<?php

namespace App\Modules\Auth\Domain\Services;

interface PasswordHasherInterface
{
    public function make(string $plain): string;
    public function check(string $plain, string $hashed): bool;
}

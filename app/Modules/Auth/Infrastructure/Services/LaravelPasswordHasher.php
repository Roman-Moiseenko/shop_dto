<?php

namespace App\Modules\Auth\Infrastructure\Services;

use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use Illuminate\Support\Facades\Hash;

class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function make(string $plain): string
    {
        return Hash::make($plain);
    }

    public function check(string $plain, string $hashed): bool
    {
        return Hash::check($plain, $hashed);
    }
}

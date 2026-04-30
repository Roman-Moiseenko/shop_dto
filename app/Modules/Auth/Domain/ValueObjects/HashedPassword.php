<?php

namespace App\Modules\Auth\Domain\ValueObjects;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use InvalidArgumentException;

final class HashedPassword
{
    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function fromPlainText(string $plain, PasswordHasherInterface $hasher): self
    {
        if (strlen($plain) < 8) {
            throw new InvalidArgumentException('Пароль должен содержать минимум 8 символов');
        }
        return new self($hasher->make($plain));
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function getHash(): string { return $this->hash; }
    public function verify(string $plain, PasswordHasherInterface $hasher): bool
    {
        return $hasher->check($plain, $this->hash);
    }
}

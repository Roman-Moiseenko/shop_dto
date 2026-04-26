<?php

namespace App\Modules\Auth\Domain\ValueObjects;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

final class HashedPassword
{
    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function fromPlainText(string $plain): self
    {
        if (strlen($plain) < 8) {
            throw new InvalidArgumentException('Пароль должен содержать минимум 8 символов');
        }
        return new self(Hash::make($plain));
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function getHash(): string { return $this->hash; }
    public function verify(string $plain): bool { return Hash::check($plain, $this->hash); }
}

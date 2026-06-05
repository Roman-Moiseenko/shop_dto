<?php

namespace App\Modules\Auth\Domain\ValueObjects;

use App\Modules\Auth\Infrastructure\Models\Client;
use App\Modules\Auth\Infrastructure\Models\Freelance;
use App\Modules\Auth\Infrastructure\Models\Staff;

enum ProfileType: string
{
    case STAFF     = 'staff';
    case FREELANCE = 'freelance';
    case CLIENT    = 'client';

    /**
     * Будущий маппинг на Eloquent-модели (только для Infrastructure)
     * @internal Этот метод не должен использоваться в Domain/Application слоях
     */
    public function getModelClass(): string
    {
        return match ($this) {
            self::STAFF     => Staff::class,
            self::FREELANCE => Freelance::class,
            self::CLIENT    => Client::class,
        };
    }

    /**
     * Полное имя Eloquent-класса → Enum
     * Используется только в Infrastructure слое (Repository)
     *
     * @param string|null $modelClass
     * @return self|null
     */
    public static function fromModelClass(?string $modelClass): ?self
    {
        return match ($modelClass) {
            Staff::class     => self::STAFF,
            Freelance::class => self::FREELANCE,
            Client::class    => self::CLIENT,
            null             => null,
            default          => throw new \DomainException(
                "Unknown profileable_type: {$modelClass}"
            ),
        };
    }
}

<?php

namespace App\Modules\Content\Domain\Entities;

final class SearchSettings
{
    public function __construct(
        public readonly bool $enabled,
        public readonly string $placeholder,
        public readonly string $actionUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: $data['enabled'] ?? false,
            placeholder: $data['placeholder'] ?? '',
            actionUrl: $data['action_url'] ?? '',
        );
    }
}

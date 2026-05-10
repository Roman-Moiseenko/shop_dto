<?php

namespace App\Modules\Content\Application\DTOs;

use App\Modules\Content\Domain\ValueObjects\Meta;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\StringType;

class MetaData extends Data
{
    public function __construct(
        #[Nullable, StringType]
        public readonly ?string $title = '',
        #[Nullable, StringType]
        public readonly ?string $description = '',
    ) {}

    public static function fromEntity(Meta $meta): self
    {
        return new self(
            title: $meta->getTitle(),
            description: $meta->getDescription(),
        );
    }
}

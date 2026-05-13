<?php

namespace App\Modules\Content\Application\DTOs\Contact;

use App\Modules\Content\Domain\Entities\ContactEntity;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\{
    Required, StringType, Max, Nullable
};

class ContactData extends Data
{
    public function __construct(
        #[Required, StringType, Max(50)]
        public readonly string $type,
        #[Required, StringType, Max(255)]
        public readonly string $value,
        #[Nullable, StringType, Max(2048)]
        public readonly ?string $link = null,
        #[Nullable, StringType, Max(36)]
        public readonly ?string $iconUuid = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $caption = null,
        #[Nullable, StringType, Max(100)]
        public readonly ?string $analyticsField = null,
    ) {}

}

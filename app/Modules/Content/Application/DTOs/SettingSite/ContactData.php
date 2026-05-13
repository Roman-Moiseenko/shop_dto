<?php

namespace App\Modules\Content\Application\DTOs\SettingSite;

use App\Modules\Content\Domain\Entities\ContactEntity;
use Spatie\LaravelData\Data;

class ContactData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $value,
        public readonly ?string $link,
        public readonly ?string $iconUuid,
        public readonly ?string $caption,
        public readonly ?string $analyticsField,
        public readonly int $sortOrder,
    ) {}

    public static function fromEntity(ContactEntity $contact): self {
        return new self(
            $contact->id,
            $contact->type,
            $contact->value,
            $contact->link,
            $contact->iconUuid,
            $contact->caption,
            $contact->analyticsField,
            $contact->sort
        );
    }
}

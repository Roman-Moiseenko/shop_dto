<?php

namespace App\Modules\Content\Application\DTOs\Contact;

use App\Modules\Content\Domain\Entities\ContactEntity;
use Spatie\LaravelData\Data;

class ContactViewData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $value,
        public readonly ?string $link,
        public readonly ?string $iconUuid,
        public readonly ?string $caption,
        public readonly ?string $analyticsField,
        public readonly int $sort,
        public readonly bool $isActive,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromEntity(ContactEntity $contact): self
    {
        return new self(
            id:             $contact->id,
            type:           $contact->type,
            value:          $contact->value,
            link:           $contact->link,
            iconUuid:       $contact->iconUuid,
            caption:        $contact->caption,
            analyticsField: $contact->analyticsField,
            sort:           $contact->sort,
            isActive:       $contact->isActive,
            createdAt:      $contact->createdAt?->format('c'),
            updatedAt:      $contact->updatedAt?->format('c'),
        );
    }
}

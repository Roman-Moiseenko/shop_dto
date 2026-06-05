<?php

namespace App\Modules\Content\Application\Actions\Contact;

use App\Modules\Content\Application\DTOs\Contact\ContactData;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Content\Domain\ValueObjects\ContactType;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

final class CreateContactUseCase
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository
    ) {}

    public function execute(ContactData $dto, UserPermission $permissions): ContactEntity
    {
        if (!$permissions->can('content.data.create')) {
            throw new AccessDeniedException();
        }

        $contact = new ContactEntity(
            type:           new ContactType($dto->type),
            value:          $dto->value,
            link:           $dto->link,
            iconUuid:       $dto->iconUuid,
            caption:        $dto->caption,
            analyticsField: $dto->analyticsField,
        );

        return $this->contactRepository->save($contact);
    }
}

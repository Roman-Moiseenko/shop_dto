<?php

namespace App\Modules\Content\Application\Actions\Contact;

use App\Modules\Content\Application\DTOs\Contact\ContactData;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Content\Domain\ValueObjects\ContactType;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use InvalidArgumentException;

final readonly class UpdateContactUseCase
{
    public function __construct(
        private ContactRepositoryInterface $contactRepository
    ) {}

    public function execute(int $id, ContactData $dto, UserPermission $permissions): ContactEntity
    {
        if (!$permissions->can('content.data.edit')) {
            throw new AccessDeniedException();
        }

        $contact = $this->contactRepository->findById($id);
        if (!$contact) {
            throw new InvalidArgumentException('Контакт не найден');
        }

        $contact->type           = new ContactType($dto->type);
        $contact->value          = $dto->value;
        $contact->link           = $dto->link;
        $contact->iconUuid       = $dto->iconUuid;
        $contact->caption        = $dto->caption;
        $contact->analyticsField = $dto->analyticsField;

        return $this->contactRepository->save($contact);
    }
}

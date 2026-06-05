<?php

namespace App\Modules\Content\Application\Actions\Contact;

use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use InvalidArgumentException;

final readonly class DeactivateContactUseCase
{
    public function __construct(private ContactRepositoryInterface $contactRepository) {}

    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.edit')) {
            throw new AccessDeniedException();
        }

        $contact = $this->contactRepository->findById($id);
        if (!$contact) {
            throw new InvalidArgumentException('Контакт не найден');
        }

        $contact->isActive = false;
        $this->contactRepository->save($contact);
    }
}

<?php

namespace App\Modules\Content\Application\Actions\Contact;

use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use InvalidArgumentException;

final readonly class ViewContactUseCase
{
    public function __construct(private ContactRepositoryInterface $contactRepository) {}

    public function execute(int $id, UserPermission $permissions): ContactEntity
    {
        if (!$permissions->can('content.data.view')) {
            throw new AccessDeniedException();
        }

        $contact = $this->contactRepository->findById($id);
        if (!$contact) {
            throw new InvalidArgumentException('Контакт не найден');
        }

        return $contact;
    }
}

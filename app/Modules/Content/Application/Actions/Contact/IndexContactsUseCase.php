<?php

namespace App\Modules\Content\Application\Actions\Contact;

use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

final readonly class IndexContactsUseCase
{
    public function __construct(private ContactRepositoryInterface $contactRepository) {}

    public function execute(UserPermission $permissions): array
    {
        if (!$permissions->can('content.data.view')) {
            throw new AccessDeniedException();
        }

        return $this->contactRepository->all(); // или findAllActive() в зависимости от требований
    }
}

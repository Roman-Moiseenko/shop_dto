<?php

namespace App\Modules\Content\Application\Actions\Contact;

use App\Modules\Content\Application\DTOs\Contact\ReorderContactData;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

final readonly class ReorderContactUseCase
{
    public function __construct(
        private ContactRepositoryInterface $contactRepository
    ) {}

    public function execute(ReorderContactData $dto, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.edit')) {
            throw new AccessDeniedException();
        }

        $this->contactRepository->updateSortOrder($dto->id, $dto->newSort);
    }
}

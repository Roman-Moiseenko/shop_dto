<?php

namespace App\Modules\Content\Application\Actions\Pages;

use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Infrastructure\Exceptions\PageNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use InvalidArgumentException;

class DeletePageUseCase
{
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository
    ) {}

    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.delete')) {
            throw new AccessDeniedException();
        }

        $page = $this->pageRepository->findById($id);
        if (!$page) throw new PageNotFoundException($id);

        $this->pageRepository->delete($id);
    }
}

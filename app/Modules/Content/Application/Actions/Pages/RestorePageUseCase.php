<?php

namespace App\Modules\Content\Application\Actions\Pages;

use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Exceptions\PageNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

class RestorePageUseCase
{
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository
    ) {}

    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.delete')) {
            throw new AccessDeniedException();
        }

        // Проверяем, что удалённая страница существует
        $page = $this->pageRepository->findById($id, withTrashed: true);
        if (!$page || !$page->isTrashed)
            throw new PageNotFoundException($id);


        $this->pageRepository->restore($id);
    }
}

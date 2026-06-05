<?php

namespace App\Modules\Content\Application\Actions\Pages;

use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Domain\Exceptions\PageNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

class ViewPageUseCase
{
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository
    ) {}

    public function execute(int $id, UserPermission $permissions): PageEntity
    {
        if (!$permissions->can('content.data.view')) {
            throw new AccessDeniedException();
        }

        $page = $this->pageRepository->findById($id);
        if (!$page) throw new PageNotFoundException($id);

        return $page;
    }
}

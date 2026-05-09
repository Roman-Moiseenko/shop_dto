<?php

namespace App\Modules\Content\Application\Actions\Pages;

use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class IndexPageUseCase
{
    public function __construct(
        private PageRepositoryInterface $pageRepository
    ) {}

    public function execute(UserPermission $permissions, int $perPage = 15): LengthAwarePaginator
    {
        if (!$permissions->can('content.data.view')) {
            throw new AccessDeniedException();
        }

        return $this->pageRepository->paginate($perPage);
    }
}

<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

final readonly class DeleteMenuUseCase
{
    public function __construct(private readonly MenuRepositoryInterface $menuRepository) {}

    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.delete')) {
            throw new AccessDeniedException();
        }

        $menu = $this->menuRepository->findById($id);
        if (!$menu) {
            throw new MenuNotFoundException('Меню не найдено');
        }

        $this->menuRepository->delete($id);
    }
}

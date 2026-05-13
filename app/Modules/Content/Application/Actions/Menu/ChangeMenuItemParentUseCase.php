<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\DTOs\Menu\ChangeMenuItemParentData;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Infrastructure\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use InvalidArgumentException;

final readonly class ChangeMenuItemParentUseCase
{
    public function __construct(private MenuItemRepositoryInterface $itemRepository) {}

    public function execute(int $itemId, ChangeMenuItemParentData $dto, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.edit')) throw new AccessDeniedException();
        $item = $this->itemRepository->findById($itemId);
        if (!$item) throw new MenuNotFoundException('Пункт меню не найден');
        $this->itemRepository->changeParent($itemId, $dto->newParentId);
    }
}

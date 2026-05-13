<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\DTOs\Menu\MenuItemUpdateData;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Domain\ValueObjects\MenuItemStyle;
use App\Modules\Content\Domain\ValueObjects\ReferenceType;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use InvalidArgumentException;

final readonly class UpdateMenuItemUseCase
{
    public function __construct(
        private MenuItemRepositoryInterface $itemRepository
    ) {}

    public function execute(int $menuId, int $itemId, MenuItemUpdateData $dto, UserPermission $permissions): MenuItemEntity
    {
        if (!$permissions->can('content.data.edit')) {
            throw new AccessDeniedException();
        }

        $item = $this->itemRepository->findById($itemId);
        if (!$item) {
            throw new InvalidArgumentException('Пункт меню не найден');
        }

        //$item->menuId = $menuId;
        $item->parentId = $dto->parentId;
        $item->title = $dto->title;
        $item->url = $dto->url;
        $item->referenceType = $dto->referenceType ? new ReferenceType($dto->referenceType) : null;
        $item->referenceId = $dto->referenceId;
        $item->iconUuid = $dto->iconUuid;
        $item->style = $dto->style ? new MenuItemStyle($dto->style) : null;
        $item->targetBlank = $dto->targetBlank;
        $item->widgetInstanceId = $dto->widgetInstanceId;

        return $this->itemRepository->save($item);
    }
}

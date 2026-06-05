<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\DTOs\Menu\MenuItemCreateData;
use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Domain\ValueObjects\ReferenceType;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

final readonly class CreateMenuItemUseCase
{
    public function __construct(private MenuItemRepositoryInterface $itemRepository) {}

    public function execute(int $menuId, MenuItemCreateData $dto, UserPermission $permissions): MenuItemEntity
    {
        if (!$permissions->can('content.data.create')) throw new AccessDeniedException();

        $item = new MenuItemEntity(
            menuId: $menuId,
            title: $dto->title,
            parentId: $dto->parentId,
            url: $dto->url,
            referenceType: $dto->referenceType ? new ReferenceType($dto->referenceType) : null,
            referenceId: $dto->referenceId,
            isActive: false,
        );

        return $this->itemRepository->save($item);
    }
}

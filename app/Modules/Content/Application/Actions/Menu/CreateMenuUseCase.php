<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\DTOs\Menu\MenuData;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;

final readonly class CreateMenuUseCase
{
    public function __construct(private MenuRepositoryInterface $menuRepository) {}

    public function execute(MenuData $dto, UserPermission $permissions): MenuEntity
    {
        if (!$permissions->can('content.data.create')) throw new AccessDeniedException();
        $menu = new MenuEntity(
            $dto->name,
            new Slug($dto->slug ?: $dto->name),
            $dto->description,
            $dto->isActive ?? true
        );
        return $this->menuRepository->save($menu);
    }
}

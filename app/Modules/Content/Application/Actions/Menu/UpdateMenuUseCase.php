<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\DTOs\Menu\MenuData;
use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Content\Domain\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;

final readonly class UpdateMenuUseCase
{
    public function __construct(private MenuRepositoryInterface $menuRepository) {}

    public function execute(int $id, MenuData $dto, UserPermission $permissions): MenuEntity
    {
        if (!$permissions->can('content.data.edit')) throw new AccessDeniedException();

        $menu = $this->menuRepository->findById($id);
        if (!$menu) throw new MenuNotFoundException('Меню не найдено');

        $menu->name = $dto->name;
        $menu->slug = new Slug($dto->slug ?: $dto->name);
        if ($dto->description !== null) $menu->description = $dto->description;
        if ($dto->isActive !== null) $dto->isActive ? $menu->activate() : $menu->deactivate();

        return $this->menuRepository->save($menu);
    }
}

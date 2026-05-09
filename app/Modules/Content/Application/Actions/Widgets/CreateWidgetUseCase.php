<?php

namespace App\Modules\Content\Application\Actions\Widgets;

use App\Modules\Content\Application\DTOs\WidgetData;
use App\Modules\Content\Application\Interfaces\WidgetRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetEntity;
use App\Modules\Content\Domain\ValueObjects\WidgetCategory;
use App\Modules\Content\Domain\ValueObjects\WidgetSchema;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

readonly class CreateWidgetUseCase
{
    public function __construct(private WidgetRepositoryInterface $widgetRepository) {}

    public function execute(WidgetData $dto, UserPermission $permissions): WidgetEntity
    {
        if (!$permissions->can('content.settings.create')) throw new AccessDeniedException();

        $widget = new WidgetEntity(
            $dto->name,
            $dto->slug,
            new WidgetCategory($dto->category),
            new WidgetSchema($dto->schema),
            $dto->description
        );

        return $this->widgetRepository->save($widget);
    }
}

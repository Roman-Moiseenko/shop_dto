<?php

namespace App\Modules\Content\Application\Actions\Public;

use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use App\Modules\Shared\Domain\ValueObjects\Slug;

class ViewPublicPageUseCase
{
    public function __construct(
        private readonly PageRepositoryInterface         $pageRepository,
        private readonly ContentBlockRepositoryInterface $blockRepository
    ) {}

    public function execute(string $slug): ?PageEntity
    {
        // Публичный показ – только активные (не удалённые и опубликованные)
        $page = $this->pageRepository->findBySlug(new Slug($slug), withTrashed: false);
        if (!$page || !$page->isPublished() || $page->isTrashed) {
            return null;
        }
        return $page;
    }

    // Можно отдельно возвращать блоки, но для соблюдения SRP оставим это в контроллере или объединим в один DTO
    public function getBlocks(PageEntity $page): array
    {
        return $this->blockRepository->listByContainer(
            ContainerType::page(),
            $page->id
        );
    }
}

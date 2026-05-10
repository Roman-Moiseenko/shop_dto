<?php

namespace App\Modules\Content\Application\Actions\Pages;

use App\Modules\Content\Application\DTOs\Page\PageUpdateData;
use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Domain\ValueObjects\ContentType;
use App\Modules\Content\Domain\ValueObjects\Meta;
use App\Modules\Content\Domain\ValueObjects\PageStatus;
use App\Modules\Content\Domain\ValueObjects\PageTemplate;
use App\Modules\Content\Infrastructure\Exceptions\PageNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

class UpdatePageUseCase
{
    public function __construct(private PageRepositoryInterface $pageRepository) {}

    public function execute(int $id, PageUpdateData $dto, UserPermission $permissions): PageEntity
    {
        if (!$permissions->can('content.data.edit')) {
            throw new AccessDeniedException();
        }

        $page = $this->pageRepository->findById($id);
        if (!$page) throw new PageNotFoundException($id);

        if ($dto->title !== null) $page->title = $dto->title;
        if ($dto->slug !== null) $page->slug = new Slug($dto->slug);
        if ($dto->contentType !== null) $page->contentType = new ContentType($dto->contentType);
        if ($dto->content !== null) $page->content = $dto->content;
        if ($dto->status !== null) $page->status = new PageStatus($dto->status);
        if ($dto->meta !== null) $page->meta = new Meta($dto->meta);
        if ($dto->template !== null) $page->template = new PageTemplate($dto->template);
        if ($dto->authorId !== null) $page->setAuthorId($dto->authorId);

        return $this->pageRepository->save($page);
    }
}

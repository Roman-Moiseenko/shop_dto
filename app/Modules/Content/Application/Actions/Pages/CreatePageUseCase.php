<?php

namespace App\Modules\Content\Application\Actions\Pages;

use App\Modules\Content\Application\DTOs\Page\PageCreateData;
use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Domain\ValueObjects\ContentType;
use App\Modules\Content\Domain\ValueObjects\Meta;
use App\Modules\Content\Domain\ValueObjects\PageStatus;
use App\Modules\Content\Domain\ValueObjects\PageTemplate;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

readonly class CreatePageUseCase
{
    public function __construct(private PageRepositoryInterface $pageRepository) {}

    public function execute(PageCreateData $dto, UserPermission $permissions): PageEntity
    {
        if (!$permissions->can('content.data.create')) {
            throw new AccessDeniedException();
        }

        $page = new PageEntity(
            $dto->title,
            new Slug($dto->slug),
            contentType: $dto->contentType ? new ContentType($dto->contentType) : null,
            content: $dto->content,
            status: $dto->status ? new PageStatus($dto->status) : null,
            meta: $dto->meta ? new Meta($dto->meta) : null,
            authorId: $dto->authorId,
            template: $dto->template ? new PageTemplate($dto->template) : null,
        );

        return $this->pageRepository->save($page);
    }
}

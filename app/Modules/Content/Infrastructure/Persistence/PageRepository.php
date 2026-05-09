<?php

namespace App\Modules\Content\Infrastructure\Persistence;

use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Domain\ValueObjects\ContentType;
use App\Modules\Content\Domain\ValueObjects\Meta;
use App\Modules\Content\Domain\ValueObjects\PageStatus;
use App\Modules\Content\Domain\ValueObjects\PageTemplate;
use App\Modules\Content\Infrastructure\Models\Page;
use App\Modules\Shared\Domain\Services\TransactionManagerInterface;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use DateTimeImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageRepository implements PageRepositoryInterface
{
    public function __construct(
        private readonly TransactionManagerInterface $transaction
    )
    {
    }

    public function save(PageEntity $page): PageEntity
    {
        return $this->transaction->execute(function () use ($page) {
            $model = $page->id ? Page::findOrFail($page->id) : new Page();

            $model->title = $page->title;
            $model->slug = (string)$page->slug;
            $model->content = $page->content;
            $model->content_type = $page->contentType->getValue();
            $model->status = $page->status->getValue();
            $model->published_at = $page->publishedAt;
            $model->meta = $page->meta ? $page->meta->toArray() : null;
            $model->template = $page->template ? $page->template->getValue() : null;
            $model->author_id = $page->getAuthorId();
            $model->save();

            return $this->hydrate($model);
        });
    }

    public function findById(int $id, bool $withTrashed = true): ?PageEntity
    {
        $query = Page::query();
        if ($withTrashed) $query->withTrashed();
        $model = $query->find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findBySlug(Slug $slug, bool $withTrashed = true): ?PageEntity
    {
        $query = Page::where('slug', (string)$slug);
        if ($withTrashed) $query->withTrashed();
        $model = $query->first();
        return $model ? $this->hydrate($model) : null;
    }

    public function slugExists(Slug $slug, ?int $excludeId = null, bool $withTrashed = true): bool
    {
        $query = Page::where('slug', (string)$slug);
        if ($withTrashed) $query->withTrashed();
        if ($excludeId) $query->where('id', '!=', $excludeId);
        return $query->exists();
    }

    public function delete(int $id): void
    {
        $this->transaction->execute(function () use ($id) {
            Page::destroy($id);
        });
    }

    public function paginate(int $perPage = 15, array $filters = [], bool $withTrashed = true): LengthAwarePaginator
    {
        $query = Page::query();
        if ($withTrashed) $query->withTrashed();
        // Фильтры (статус, тип контента и т.д.) можно добавить позже
        return $query->paginate($perPage)
            ->through(fn($model) => $this->hydrate($model));
    }

    public function forceDelete(int $id): void
    {
        $this->transaction->execute(function () use ($id) {
            $page = Page::withTrashed()->findOrFail($id);
            $page->forceDelete();
        });
    }

    public function restore(int $id): void
    {
        $this->transaction->execute(function () use ($id) {
            $page = Page::withTrashed()->findOrFail($id);
            $page->restore();
        });
    }

    private function hydrate(Page $model): PageEntity
    {
        $page = new PageEntity(
            $model->title,
            new Slug($model->slug),
            new ContentType($model->content_type),
            $model->content,
            new PageStatus($model->status),
            $model->meta ? new Meta($model->meta) : null,
            $model->author_id,
            $model->template ? new PageTemplate($model->template) : null,
        );
        $page->id = $model->id;
        if ($model->published_at) {
            $page->publish(DateTimeImmutable::createFromMutable($model->published_at));
        }
        if ($model->deleted_at) {
            $page->deletedAt = DateTimeImmutable::createFromMutable($model->deleted_at);
        }
        $page->setCreatedAt(DateTimeImmutable::createFromMutable($model->created_at));
        $page->setUpdatedAt(DateTimeImmutable::createFromMutable($model->updated_at));

        return $page;
    }
}

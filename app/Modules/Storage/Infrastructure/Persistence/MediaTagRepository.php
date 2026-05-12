<?php

namespace App\Modules\Storage\Infrastructure\Persistence;

use App\Modules\Shared\Domain\Services\TransactionManagerInterface;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use App\Modules\Storage\Domain\ValueObjects\TagName;
use App\Modules\Storage\Infrastructure\Models\MediaTag;
use DateTimeImmutable;
class MediaTagRepository implements MediaTagRepositoryInterface
{
    public function __construct(
        private readonly TransactionManagerInterface $transaction
    ) {}

    public function save(MediaTagEntity $tag): MediaTagEntity
    {
        return $this->transaction->execute(function () use ($tag) {
            $model = $tag->id ? MediaTag::findOrFail($tag->id) : new MediaTag();
            $model->name = $tag->name->getValue();
            $model->slug = (string) $tag->slug;
            $model->save();

            return $this->hydrate($model);
        });
    }

    public function findById(int $id): ?MediaTagEntity
    {
        $model = MediaTag::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findBySlug(Slug $slug): ?MediaTagEntity
    {
        $model = MediaTag::where('slug', (string) $slug)->first();
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): void
    {
        $this->transaction->execute(function () use ($id) {
            MediaTag::destroy($id);
        });
    }

    public function all(): array
    {
        return MediaTag::orderBy('name')->get()
            ->map(fn($model) => $this->hydrate($model))
            ->all();
    }

    private function hydrate(MediaTag $model): MediaTagEntity
    {
        $tag = new MediaTagEntity(
            new TagName($model->name),
            new Slug($model->slug),
        );
        $tag->id = $model->id;
        $tag->createdAt = DateTimeImmutable::createFromMutable($model->created_at);
        $tag->updatedAt = DateTimeImmutable::createFromMutable($model->updated_at);

        return $tag;
    }
}

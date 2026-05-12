<?php

namespace App\Modules\Storage\Infrastructure\Persistence;

use App\Modules\Shared\Domain\Services\TransactionManagerInterface;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;
use App\Modules\Storage\Domain\Entities\GalleryEntity;
use App\Modules\Storage\Domain\ValueObjects\GalleryName;
use App\Modules\Storage\Infrastructure\Models\Gallery;
use DateTimeImmutable;

class GalleryRepository implements GalleryRepositoryInterface
{
    public function __construct(
        private readonly TransactionManagerInterface $transaction
    ) {}

    public function save(GalleryEntity $gallery): GalleryEntity
    {
        return $this->transaction->execute(function () use ($gallery) {
            $model = $gallery->id ? Gallery::findOrFail($gallery->id) : new Gallery();
            $model->name = $gallery->name->getValue();
            $model->slug = (string) $gallery->slug;
            $model->description = $gallery->description;
            $model->is_active = $gallery->isActive;
            $model->save();

            return $this->hydrate($model);
        });
    }

    public function findById(int $id): ?GalleryEntity
    {
        $model = Gallery::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findBySlug(Slug $slug): ?GalleryEntity
    {
        $model = Gallery::where('slug', (string) $slug)->first();
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): void
    {
        $this->transaction->execute(function () use ($id) {
            Gallery::destroy($id);
        });
    }

    public function all(): array
    {
        return Gallery::orderBy('name')->get()
            ->map(fn($model) => $this->hydrate($model))
            ->all();
    }

    private function hydrate(Gallery $model): GalleryEntity
    {
        $gallery = new GalleryEntity(
            new GalleryName($model->name),
            new Slug($model->slug),
            $model->description,
            $model->is_active
        );
        $gallery->id = $model->id;
        $gallery->createdAt = DateTimeImmutable::createFromMutable($model->created_at);
        $gallery->updatedAt = DateTimeImmutable::createFromMutable($model->updated_at);

        return $gallery;
    }
}

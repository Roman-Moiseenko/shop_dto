<?php

namespace App\Modules\Content\Infrastructure\Persistence;

use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuEntity;
use App\Modules\Content\Infrastructure\Models\Menu;
use App\Modules\Shared\Domain\Services\TransactionManagerInterface;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use DateTimeImmutable;

class MenuRepository implements MenuRepositoryInterface
{
    public function __construct(
        private readonly TransactionManagerInterface $transaction
    ) {}

    public function save(MenuEntity $menu): MenuEntity
    {
        return $this->transaction->execute(function () use ($menu) {
            $model = $menu->id ? Menu::findOrFail($menu->id) : new Menu();
            $model->name = $menu->name;
            $model->slug = (string) $menu->slug;
            $model->description = $menu->description;
            $model->is_active = $menu->isActive;
            $model->save();

            return $this->hydrate($model);
        });
    }

    public function findById(int $id): ?MenuEntity
    {
        $model = Menu::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findBySlug(Slug $slug): ?MenuEntity
    {
        $model = Menu::where('slug', (string) $slug)->first();
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): void
    {
        $this->transaction->execute(function () use ($id) {
            Menu::destroy($id);
        });
    }

    public function all(): array
    {
        return Menu::orderBy('name')->get()
            ->map(fn($model) => $this->hydrate($model))
            ->all();
    }

    private function hydrate(Menu $model): MenuEntity
    {
        $menu = new MenuEntity(
            $model->name,
            new Slug($model->slug),
            $model->description,
            $model->is_active
        );
        $menu->id = $model->id;
        $menu->createdAt = DateTimeImmutable::createFromMutable($model->created_at);
        $menu->updatedAt = DateTimeImmutable::createFromMutable($model->updated_at);

        return $menu;
    }
}

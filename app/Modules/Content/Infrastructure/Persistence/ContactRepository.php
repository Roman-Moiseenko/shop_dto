<?php

namespace App\Modules\Content\Infrastructure\Persistence;

use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Content\Infrastructure\Models\Contact;
use App\Modules\Shared\Domain\Services\TransactionManagerInterface;
use DateTimeImmutable;

class ContactRepository implements ContactRepositoryInterface
{
    public function __construct(
        private readonly TransactionManagerInterface $transaction
    ) {}

    public function save(ContactEntity $contact): ContactEntity
    {
        return $this->transaction->execute(function () use ($contact) {
            $model = $contact->id ? Contact::findOrFail($contact->id) : new Contact();

            // При создании, если sort не задан явно, ставим в конец списка
            if (!$contact->id && $contact->sort === 0) {
                $maxSort = Contact::max('sort') ?? -1;
                $contact->sort = $maxSort + 1;
            }

            $model->type            = $contact->type;
            $model->value           = $contact->value;
            $model->link            = $contact->link;
            $model->icon_uuid       = $contact->iconUuid;
            $model->caption         = $contact->caption;
            $model->analytics_field = $contact->analyticsField;
            // sort не обновляем при редактировании — для этого есть отдельный UseCase
            $model->is_active       = $contact->isActive;
            $model->save();

            return $this->hydrate($model);
        });
    }

    public function findById(int $id): ?ContactEntity
    {
        $model = Contact::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): void
    {
        $this->transaction->execute(function () use ($id) {
            $contact = Contact::findOrFail($id);
            $deletedSort = $contact->sort;
            $contact->delete();

            // Пересчитываем sort у оставшихся контактов, чтобы не было пропусков
            Contact::where('sort', '>', $deletedSort)->decrement('sort');
        });
    }

    public function findAllActive(): array
    {
        return Contact::where('is_active', true)
            ->orderBy('sort')
            ->get()
            ->map(fn($model) => $this->hydrate($model))
            ->all();
    }

    public function all(): array
    {
        return Contact::orderBy('sort')
            ->get()
            ->map(fn($model) => $this->hydrate($model))
            ->all();
    }

    /**
     * Перемещает контакт на новую позицию и сдвигает остальные.
     */
    public function updateSortOrder(int $contactId, int $newSort): void
    {
        $this->transaction->execute(function () use ($contactId, $newSort) {
            $contact = Contact::findOrFail($contactId);
            $oldSort = $contact->sort;

            if ($oldSort === $newSort) return;

            // Сдвигаем промежуточные элементы
            if ($newSort > $oldSort) {
                Contact::where('sort', '>', $oldSort)
                    ->where('sort', '<=', $newSort)
                    ->decrement('sort');
            } else {
                Contact::where('sort', '>=', $newSort)
                    ->where('sort', '<', $oldSort)
                    ->increment('sort');
            }

            $contact->sort = $newSort;
            $contact->save();
        });
    }

    private function hydrate(Contact $model): ContactEntity
    {
        $contact = new ContactEntity(
            type:           $model->type,
            value:          $model->value,
            link:           $model->link,
            iconUuid:       $model->icon_uuid,
            caption:        $model->caption,
            analyticsField: $model->analytics_field,
            sort:           $model->sort,
            isActive:       $model->is_active,
        );
        $contact->id        = $model->id;
        $contact->createdAt = DateTimeImmutable::createFromMutable($model->created_at);
        $contact->updatedAt = DateTimeImmutable::createFromMutable($model->updated_at);

        return $contact;
    }
}

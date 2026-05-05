<?php

namespace App\Modules\Auth\Infrastructure\Persistence;

use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Infrastructure\Models\Freelance;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Infrastructure\Models\User;
use DateTimeImmutable;
use Illuminate\Pagination\LengthAwarePaginator;

class FreelanceRepository implements FreelanceRepositoryInterface
{
    use HydratesUserEntity;
    public function save(FreelanceEntity $freelance): FreelanceEntity
    {
        $model = $freelance->id ? Freelance::find($freelance->id) : new Freelance();

        $model->last_name = $freelance->fullName->getLastName();
        $model->first_name = $freelance->fullName->getFirstName();
        $model->middle_name = $freelance->fullName->getMiddleName();
        $model->position = $freelance->position;
        $model->personal_phone = $freelance->personalPhone;
        $model->personal_email = $freelance->personalEmail;
        $model->hire_date = $freelance->hireDate;
        $model->termination_date = $freelance->terminationDate;
        $model->telegram_chat_id = $freelance->telegramChatId;
        $model->max_chat_id = $freelance->maxChatId;
        $model->notes = $freelance->notes;

        $model->save();
        return $this->hydrate($model);
    }

    public function findById(int $id): ?FreelanceEntity
    {
        $model = Freelance::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findByUserId(int $userId): ?FreelanceEntity
    {
        $user = User::find($userId);
        if (!$user || $user->profileable_type !== Freelance::class) {
            return null;
        }
        $model = Freelance::find($user->profileable_id);
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): bool
    {
        $model = Freelance::find($id);
        if (!$model) {
            return false;
        }
        return $model->delete();
    }
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Freelance::with('user')
            ->paginate($perPage)
            ->through(fn ($model) => $this->hydrate($model));
    }
    /**
     * @throws \DateMalformedStringException
     */
    private function hydrate(Freelance $model): FreelanceEntity
    {

        $fullName = new FullName($model->full_name); // предполагаем, что FullName умеет парсить строку
        $freelance = new FreelanceEntity(
            $fullName,
            $model->position,
        );
        $freelance->id = $model->id;

        if ($model->personal_phone) $freelance->personalPhone = new PhoneNumber($model->personal_phone);
        if ($model->personal_email) $freelance->personalEmail = new Email($model->personal_email);

        if ($model->termination_date) {
            $freelance->terminate(DateTimeImmutable::createFromMutable($model->termination_date));
        } else {
            $freelance->terminationDate = null;
        }
        if ($model->hire_date) $freelance->hireDate = DateTimeImmutable::createFromMutable($model->hire_date);

        if ($model->telegram_chat_id) $freelance->telegramChatId = $model->telegram_chat_id;
        if ($model->max_chat_id) $freelance->maxChatId = $model->max_chat_id;
        if ($model->notes) $freelance->notes = $model->notes;

        $freelance->user = $this->hydrateUser($model->user);


        return $freelance;
    }
}

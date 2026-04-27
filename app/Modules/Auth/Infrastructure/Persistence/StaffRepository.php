<?php

namespace App\Modules\Auth\Infrastructure\Persistence;

use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Domain\Entities\StaffEntity;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Infrastructure\Models\Staff;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Infrastructure\Models\User;
use DateTimeImmutable;

class StaffRepository implements StaffRepositoryInterface
{
    public function save(StaffEntity $staff): StaffEntity
    {
        $model = $staff->id ? Staff::find($staff->id) : new Staff();

        $model->last_name = $staff->fullName->getLastName();
        $model->first_name = $staff->fullName->getFirstName();
        $model->middle_name = $staff->fullName->getMiddleName();
        $model->position = $staff->position;
        $model->department = $staff->department;
        $model->work_phone = $staff->workPhone;
        $model->personal_phone = $staff->personalPhone;
        $model->work_email = $staff->workEmail;
        $model->hire_date = $staff->hireDate;
        $model->termination_date = $staff->terminationDate;
        $model->birth_date = $staff->birthDate;
        $model->telegram_chat_id = $staff->telegramChatId;
        $model->max_chat_id = $staff->maxChatId;
        $model->notes = $staff->notes;

        $model->save();
        return $this->hydrate($model);
    }

    public function findById(int $id): ?StaffEntity
    {
        $model = Staff::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findByUserId(int $userId): ?StaffEntity
    {
        $user = User::find($userId);
        if (!$user || $user->profileable_type !== Staff::class) {
            return null;
        }
        $model = Staff::find($user->profileable_id);
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): bool
    {
        $model = Staff::find($id);
        if (!$model) {
            return false;
        }
        return $model->delete();
    }

    private function hydrate(Staff $model): StaffEntity
    {

        $fullName = new FullName($model->full_name); // предполагаем, что FullName умеет парсить строку
        $staff = new StaffEntity(
            $fullName,
            $model->position,
        );
        $staff->id = $model->id;

        if ($model->department) $staff->department = $model->department;
        if ($model->work_phone) $staff->workPhone = new PhoneNumber($model->work_phone);
        if ($model->personal_phone) $staff->personalPhone = new PhoneNumber($model->personal_phone);
        if ($model->work_email) $staff->workEmail = new Email($model->work_email);

        if ($model->termination_date) {
            $staff->terminate(DateTimeImmutable::createFromMutable($model->termination_date));
        } else {
            $staff->terminationDate = null;
        }
        if ($model->birth_date) $staff->birthDate = DateTimeImmutable::createFromMutable($model->birth_date);
        if ($model->hire_date) $staff->hireDate = DateTimeImmutable::createFromMutable($model->hire_date);

        if ($model->telegram_chat_id) $staff->telegramChatId = $model->telegram_chat_id;
        if ($model->max_chat_id) $staff->maxChatId = $model->max_chat_id;
        if ($model->notes) $staff->notes = $model->notes;

        if ($model->user) {
            $staff->user = new UserEntity(
                new Email($model->user->email),
                HashedPassword::fromHash($model->user->password),
            );
            $staff->user->id = $model->user->id;
            $staff->user->roles = $model->user->getRoleNames()->toArray();
        }
        return $staff;
    }
}

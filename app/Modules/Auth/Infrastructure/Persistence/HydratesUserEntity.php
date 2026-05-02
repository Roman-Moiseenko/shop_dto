<?php

namespace App\Modules\Auth\Infrastructure\Persistence;

use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Infrastructure\Models\User;

trait HydratesUserEntity
{
    /**
     * @throws \DateMalformedStringException
     */
    protected function hydrateUser(?User $model): ?UserEntity
    {
        if (!$model) return null;

        $user = new UserEntity(
            new Email($model->email),
            HashedPassword::fromHash($model->password)
        );
        $user->id = $model->id;
        if ($model->banned_at) {
            $user->ban(); // или setBannedAt
        }
        $user->roles =  $model->getRoleNames()->toArray();
        $user->emailVerifiedAt = new \DateTimeImmutable($model->email_verified_at);

        return $user;
    }
}

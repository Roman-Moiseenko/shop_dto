<?php

namespace App\Modules\Auth\Infrastructure\Persistence;

use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Infrastructure\Models\EmailVerification;
use App\Modules\Auth\Infrastructure\Models\Staff;
use App\Modules\Auth\Infrastructure\Models\User;

use DateTimeImmutable;
use Illuminate\Http\Request;

class UserRepository implements UserRepositoryInterface
{
    public function save(UserEntity $user): UserEntity
    {
        $model = $user->id
            ? User::find($user->id)
            : new User();

        $model->email = (string)$user->email;

        $model->password = $user->getPasswordHash();
        $model->email_verified_at = $user->emailVerifiedAt;
        $model->remember_token = $user->rememberToken;
        $model->profileable_type = $user->profileableType;
        $model->profileable_id = $user->profileableId;
        $model->banned_at = $user->getBannedAt();
        //$model->
        $model->syncRoles($user->roles);
        $model->save();
        return $this->hydrate($model);
    }

    public function findByEmail(Email $email): ?UserEntity
    {
        $model = User::where('email', (string) $email)->first();
        return $model ? $this->hydrate($model) : null;
    }

    public function findById(int $id): ?UserEntity
    {
        $model = User::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findByStaffId(int $id): ?UserEntity
    {
        $model = User::where('profileable_type', Staff::class)->where('profileable_id', $id)->first();
        return $model ? $this->hydrate($model) : null;
    }

    public function emailExists(Email $email, ?int $excludeId = null): bool
    {
        $query = User::where('email', (string) $email);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    private function hydrate(User $model): UserEntity
    {
        $user = new UserEntity(
            new Email($model->email),
            HashedPassword::fromHash($model->password),
        );
        $user->id = $model->id;
        if ($model->email_verified_at) {
            $user->emailVerifiedAt = DateTimeImmutable::createFromMutable($model->email_verified_at);
        }
        $user->rememberToken = $model->remember_token;
        $user->setProfile($model->profileable_type, $model->profileable_id);

        if ($model->banned_at) {
            $user->setBannedAt(DateTimeImmutable::createFromMutable($model->banned_at));
        }
        $user->roles = $model->getRoleNames()->toArray();

        return $user;
    }

    public function fromRequest(Request $request): ?UserEntity
    {
        $id = $request->user()->id;
        return $this->findById($id);
    }

    public function saveEmailVerification(int $userId, Email $newEmail, string $token, ?\DateTimeImmutable $expiresAt = null): void
    {
        EmailVerification::create([
            'user_id' => $userId,
            'new_email' => (string) $newEmail,
            'token' => $token,
            'expires_at' => $expiresAt ?? now()->addHour(),
        ]);
    }

    public function findEmailVerificationByToken(string $token): ?object
    {
        $model = EmailVerification::where('token', $token)->first();
        if (!$model) return null;
        return (object) [
            'user_id' => $model->user_id,
            'new_email' => $model->new_email,
            'expires_at' => $model->expires_at,
        ];
    }

    public function deleteEmailVerification(string $token): void
    {
        EmailVerification::where('token', $token)->delete();
    }
}

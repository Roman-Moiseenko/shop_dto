<?php

namespace App\Modules\Auth\Presentation\Http\Resources;
use App\Modules\Auth\Infrastructure\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Domain\Entities\UserEntity as DomainUser;
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var DomainUser $user */
        $user = $this->resource;

        return [
            'id' => $user->id,
            'email' => (string)$user->email,
            'email_verified_at' => $user->emailVerifiedAt?->format('c'),
            'profileable_type' => $user->profileableType,
            'profileable_id' => $user->profileableId,
            'roles' => $user->roles,
            'permissions' => $this->getPermissions($user),
            'is_admin' => $user->isAdmin(),
        ];
    }

    private function getPermissions($user): array
    {
        // Получаем все разрешения пользователя (через Spatie)
        if ($user->id) {
            $model = User::find($user->id);
            return $model->getAllPermissions()->pluck('name')->toArray();
        }
        return [];
    }
}

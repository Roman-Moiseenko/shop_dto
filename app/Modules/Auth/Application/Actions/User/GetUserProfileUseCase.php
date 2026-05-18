<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\User\UserProfileData;
use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Infrastructure\Models\Freelance;
use App\Modules\Auth\Infrastructure\Models\Staff;
use App\Modules\Auth\Infrastructure\Models\User;

final class GetUserProfileUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly StaffRepositoryInterface $staffRepository,
        private readonly FreelanceRepositoryInterface $freelanceRepository,
    ) {}

    public function execute(int $userId): UserProfileData
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        $fullName = null;
        $position = null;

        // Загружаем Eloquent‑модель, чтобы узнать тип профиля
        $eloquentUser = User::find($userId);

        if ($eloquentUser->profileable_type === Staff::class) {
            $staff = $this->staffRepository->findById($eloquentUser->profileable_id);
            if ($staff) {
                $fullName = (string) $staff->fullName->getValue();
                $position = $staff->position;
            }
        } elseif ($eloquentUser->profileable_type === Freelance::class) {
            $freelance = $this->freelanceRepository->findById($eloquentUser->profileable_id);
            if ($freelance) {
                $fullName = (string) $freelance->fullName->getValue();
                $position = $freelance->position;
            }
        }

        return new UserProfileData(
            id: $user->id,
            fullName: $fullName,
            position: $position,
            roles: $user->roles,
            permissions: $user->permissions,
        );
    }
}

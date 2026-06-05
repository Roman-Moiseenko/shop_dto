<?php

namespace App\Modules\Auth\Application\Actions\User;

use App\Modules\Auth\Application\DTOs\User\UserProfileData;
use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;

final readonly class GetUserProfileUseCase
{
    public function __construct(
        private UserRepositoryInterface      $userRepository,
        private StaffRepositoryInterface     $staffRepository,
        private FreelanceRepositoryInterface $freelanceRepository,
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
        $user = $this->userRepository->findById($userId);

        if ($user->isStaff()) {
            $staff = $this->staffRepository->findById($user->profileableId);
            if ($staff) {
                $fullName = (string) $staff->fullName->getValue();
                $position = $staff->position;
            }
        } elseif ($user->isFreelance()) {
            $freelance = $this->freelanceRepository->findById($user->profileableId);
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

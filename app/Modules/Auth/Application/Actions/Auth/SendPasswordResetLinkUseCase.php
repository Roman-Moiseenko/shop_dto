<?php

namespace App\Modules\Auth\Application\Actions\Auth;

use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\ValueObjects\Email;
use Illuminate\Support\Facades\Password;

readonly class SendPasswordResetLinkUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function execute(string $email): string
    {
        $emailVO = new Email($email);
        $user = $this->userRepository->findByEmail($emailVO);

        if (!$user) {
            return Password::INVALID_USER;
        }

        return Password::sendResetLink(['email' => $email]);
    }
}

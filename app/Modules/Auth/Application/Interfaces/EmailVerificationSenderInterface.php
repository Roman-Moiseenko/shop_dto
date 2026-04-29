<?php

namespace App\Modules\Auth\Application\Interfaces;

interface EmailVerificationSenderInterface
{
    /**
     * Отправляет письмо с подтверждением на указанный адрес.
     * Возвращает токен (или идентификатор), который потом используется для верификации.
     */
    public function sendVerificationEmail(string $email): string;
}

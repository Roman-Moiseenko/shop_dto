<?php

namespace App\Modules\Mailing\Infrastructure\Services;

use App\Modules\Shared\Application\Interfaces\Mail\MailServiceInterface;
use App\Modules\Shared\Domain\Entities\Mail\Recipient;
class FakeMailService implements MailServiceInterface
{
    private static array $lastSent = [];

    public function send(string $templateName, array $data, Recipient $recipient): void
    {
        // Сохраняем информацию о последней отправке
        self::$lastSent = [
            'template' => $templateName,
            'recipient' => $recipient->email,
            'data' => $data,
        ];

        // Дополнительно можно записать в лог Laravel
        \Illuminate\Support\Facades\Log::info('Fake mail sent', self::$lastSent);
    }

    public static function getLastSent(): ?array
    {
        return self::$lastSent;
    }
}

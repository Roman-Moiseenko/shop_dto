<?php

namespace App\Modules\Shared\Infrastructure\Services;

use App\Modules\Shared\Application\Interfaces\Mail\MailServiceInterface;
use App\Modules\Shared\Domain\Entities\Mail\Recipient;

//TODO Перенести в модуль почты
class MailService implements MailServiceInterface
{

    public function send(string $templateName, array $data, Recipient $recipient): void
    {
        // TODO: Implement send() method.
    }
}

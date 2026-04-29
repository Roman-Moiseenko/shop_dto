<?php

namespace App\Modules\Shared\Application\Interfaces\Mail;

use App\Modules\Shared\Domain\Entities\Mail\Recipient;

interface MailServiceInterface
{
    public function send(string $templateName, array $data, Recipient $recipient): void;
}

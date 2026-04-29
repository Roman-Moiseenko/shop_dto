<?php

namespace App\Modules\Shared\Domain\Entities\Mail;

class Recipient
{
    public function __construct(
        public string $email,
        public ?string $name = null
    ) {}
}

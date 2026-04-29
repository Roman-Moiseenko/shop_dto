<?php

namespace App\Modules\Shared\Domain\Entities\Mail;

class Attachment
{
    public function __construct(
        public string $path,
        public ?string $name = null
    ) {}
}

<?php

namespace App\Modules\Shared\Domain\Exceptions;

use Throwable;

class AccessDeniedException extends \DomainException {

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        if (empty($message)) $message = "Access Denied";

        parent::__construct($message, $code, $previous);
    }
}

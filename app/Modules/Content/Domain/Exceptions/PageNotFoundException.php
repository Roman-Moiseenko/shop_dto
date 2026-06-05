<?php

namespace App\Modules\Content\Domain\Exceptions;

class PageNotFoundException extends \DomainException
{
    public function __construct(int $id = 0, ?string $slug = null)
    {
        $message = 'Страница не найдена';
        if ($id) $message .= " (ID: {$id})";
        if ($slug) $message .= " (slug: {$slug})";
        parent::__construct($message);
    }
}

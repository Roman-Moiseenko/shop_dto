<?php

namespace App\Modules\Shared\Application\Interfaces;

use Closure;

/**
 * Интерфейс для изоляции фасадов для тестирования
 */
interface TransactionManagerInterface
{
    public function execute(Closure $callback): mixed;
}


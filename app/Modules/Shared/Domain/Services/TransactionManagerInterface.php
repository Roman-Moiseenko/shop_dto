<?php

namespace App\Modules\Shared\Domain\Services;

use Closure;

/**
 * Интерфейс для изоляции фасадов для тестирования
 */
interface TransactionManagerInterface
{
    public function execute(Closure $callback): mixed;
}

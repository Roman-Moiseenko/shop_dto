<?php

namespace App\Modules\Shared\Infrastructure\Services;
use App\Modules\Shared\Application\Interfaces\TransactionManagerInterface;
use Closure;
use Illuminate\Support\Facades\DB;

class LaravelTransactionManager implements TransactionManagerInterface
{
    public function execute(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}

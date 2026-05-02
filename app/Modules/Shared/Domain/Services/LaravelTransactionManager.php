<?php

namespace App\Modules\Shared\Domain\Services;
use Closure;
use Illuminate\Support\Facades\DB;
class LaravelTransactionManager implements TransactionManagerInterface
{
    public function execute(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}

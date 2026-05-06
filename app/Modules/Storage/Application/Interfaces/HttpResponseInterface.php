<?php

namespace App\Modules\Storage\Application\Interfaces;

interface HttpResponseInterface
{
    public function successful(): bool;
    public function body(): string;
    public function header(string $key): ?string;
}

<?php

namespace App\Modules\Storage\Infrastructure\Services;

use App\Modules\Storage\Application\Interfaces\HttpResponseInterface;

class LaravelHttpResponse implements HttpResponseInterface
{
    public function __construct(private $response) {}

    public function successful(): bool
    {
        return $this->response->successful();
    }

    public function body(): string
    {
        return $this->response->body();
    }

    public function header(string $key): ?string
    {
        return $this->response->header($key);
    }
}

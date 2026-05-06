<?php

namespace App\Modules\Storage\Application\Interfaces;

interface HttpClientInterface
{
    public function get(string $url): HttpResponseInterface;
}

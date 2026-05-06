<?php

namespace App\Modules\Storage\Infrastructure\Services;

use App\Modules\Storage\Application\Interfaces\HttpClientInterface;
use App\Modules\Storage\Application\Interfaces\HttpResponseInterface;
use Illuminate\Support\Facades\Http;
class LaravelHttpClient implements HttpClientInterface
{
    public function get(string $url): HttpResponseInterface
    {
        $response = Http::get($url);
        return new LaravelHttpResponse($response);
    }

}

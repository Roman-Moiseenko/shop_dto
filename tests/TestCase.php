<?php

namespace Tests;

use App\Modules\Auth\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        // Загружаем только необходимые модули (Auth)
        $app->register(AuthServiceProvider::class);
        $app->useConfigPath(realpath(__DIR__ . '/config'));
        // Остальные модули не регистрируются в тестовом окружении



        return $app;
    }
}

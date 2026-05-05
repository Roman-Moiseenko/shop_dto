<?php

use App\Modules\Auth\Infrastructure\Exceptions\StaffNotFoundException;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Shared\Presentation\Http\Middlewares\LoadUserPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            LoadUserPermission::class, //Используем свой мидлвейр для проверки доступа
        ])->alias([
            'role' => RoleMiddleware::class, //Используем роли Spatie для защиты маршрутов
            //'permission' => PermissionMiddleware::class,
           //'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AccessDeniedException $e, $request) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        });
    })->create();

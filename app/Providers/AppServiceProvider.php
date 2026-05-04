<?php

namespace App\Providers;

use App\Modules\Auth\Application\Services\Utils;
use App\Modules\Mailing\Infrastructure\Services\FakeMailService;
use App\Modules\Shared\Application\Interfaces\Mail\MailServiceInterface;
use App\Modules\Shared\Application\Interfaces\UserPermissionRepositoryInterface;
use App\Modules\Shared\Infrastructure\Persistence\UserPermissionRepositoryFromAuth;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //Регистрируем почтовый сервис для всех
        if (app()->environment('local', 'testing')) {
            $this->app->bind(MailServiceInterface::class, FakeMailService::class);
        }

        //Получение доступа для всех модулей
        $this->app->bind(
            UserPermissionRepositoryInterface::class,
            UserPermissionRepositoryFromAuth::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}

<?php
use App\Modules\Auth\Presentation\Http\Controllers\Api\ClientController;
use App\Modules\Auth\Presentation\Http\Controllers\Api\FreelanceController;
use App\Modules\Auth\Presentation\Http\Controllers\Api\RoleController;
use App\Modules\Auth\Presentation\Http\Controllers\Api\StaffController;
use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Presentation\Http\Controllers\Api\AuthController;

Route::prefix('v1/auth')->group(function () {

    //Аутентификация
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        //Route::get('/user', [AuthController::class, 'user']);
    });


    //Сотрудники Staff
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::apiResource('staff', StaffController::class);
        Route::post('/staff/{id}/user', [StaffController::class, 'user']);
    });

    //Внештатные сотрудники Freelance
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::apiResource('freelance', FreelanceController::class);
        Route::post('/freelance/{id}/user', [FreelanceController::class, 'user']);
    });

    //Клиенты Client
    Route::middleware(['auth:sanctum'])->group(function () {
        // Админские маршруты для управления клиентами
        Route::middleware(['role:admin'])->group(function () {
            Route::apiResource('client', ClientController::class);

            Route::post('/client/{id}/register', [ClientController::class, 'register']);
        });

        // Клиент может управлять своим профилем
        Route::post('/credentials', [ClientController::class, 'credentials']); //смена регистр.данных

        Route::get('/client/profile', [ClientController::class, 'profile']);
        Route::put('/client/profile', [ClientController::class, 'updateProfile']);
    });
    ///Регистрация клиента восстановление пароля
    Route::group([
        'prefix' => 'client',
    ], function () {
        Route::post('/registration', [ClientController::class, 'registration']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    //Внештатные сотрудники Freelance
});

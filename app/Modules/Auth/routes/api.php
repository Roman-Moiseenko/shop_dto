<?php
use App\Modules\Auth\Presentation\Http\Controllers\Api\ClientController;
use App\Modules\Auth\Presentation\Http\Controllers\Api\FreelanceController;
use App\Modules\Auth\Presentation\Http\Controllers\Api\RoleController;
use App\Modules\Auth\Presentation\Http\Controllers\Api\StaffController;
use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Presentation\Http\Controllers\Api\AuthController;

//middleware(['load.permissions'])->
Route::prefix('v1/auth')->group(function () {
    //Без доступа
    //Аутентификация
    Route::post('/login', [AuthController::class, 'login']);
    ///Регистрация клиента восстановление пароля
    Route::group([
        'prefix' => 'client',
    ], function () {
        Route::post('/registration', [ClientController::class, 'registration']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    //С доступом
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        //Клиенты Client
        // Клиент может управлять своим профилем
        Route::post('/client/credentials', [ClientController::class, 'credentials']); //смена регистр.данных
        Route::get('/client/profile', [ClientController::class, 'profile']);
        Route::put('/client/profile', [ClientController::class, 'updateProfile']);
        // Админские маршруты для управления клиентами
        Route::middleware(['role:admin|staff'])->group(function () {
            Route::apiResource('client', ClientController::class);
            Route::post('/client/{id}/register', [ClientController::class, 'register']);
        });

        // маршруты для управления сотрудниками
        Route::middleware(['role:admin|staff'])->group(function () {
            Route::get('/user', [AuthController::class, 'profile']);
            //Сотрудники Staff
            Route::apiResource('staff', StaffController::class);
            Route::post('/staff/{id}/user', [StaffController::class, 'user']);

            //Внештатные сотрудники Freelance
            Route::apiResource('freelance', FreelanceController::class);
            Route::post('/freelance/{id}/user', [FreelanceController::class, 'user']);

            //Управление ролями
            Route::apiResource('role', RoleController::class)->except(['create', 'edit']);
            Route::get('permission/grouped', [RoleController::class, 'permissions']);
        });
    });
});

<?php

use App\Modules\Storage\Presentation\Http\Controllers\Api\ClientMediaController;
use App\Modules\Storage\Presentation\Http\Controllers\Api\MediaController;

Route::prefix('v1/storage')->group(function () {
    //Для фронтенда сайта (без авторизации)
    Route::get('/media/{uuid}', [MediaController::class, 'show']);

    // Административные операции (только admin|staff)
    Route::middleware(['auth:sanctum', 'role:admin|staff']) ->group(function () {

            Route::post('/media/upload', [MediaController::class, 'upload']);
            Route::post('/media/download', [MediaController::class, 'download']); //Загрузка в хранилище по url
            Route::get('/media', [MediaController::class, 'index']); //Получаем медиа для сущности
            //Работа с медиа по id - изменение параметров сортировки, title и другие
            Route::put('/media/{id}', [MediaController::class, 'update']); //Редактировать поля медиа
            Route::delete('/media/{id}', [MediaController::class, 'destroy']); //Удаление по id

        });

        //Для загрузки изображений клиентами - фото для отзыва
        Route::middleware(['auth:sanctum', 'role:client'])->group(function () {
            Route::post('/media/client', [ClientMediaController::class, 'store']);
            Route::delete('/media/client/{uuid}', [ClientMediaController::class, 'destroy']); //? Возможно и не понадобится
        });

});

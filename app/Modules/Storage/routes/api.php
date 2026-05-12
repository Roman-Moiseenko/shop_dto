<?php

use App\Modules\Storage\Presentation\Http\Controllers\Api\ClientMediaController;
use App\Modules\Storage\Presentation\Http\Controllers\Api\GalleryController;
use App\Modules\Storage\Presentation\Http\Controllers\Api\MediaController;
use App\Modules\Storage\Presentation\Http\Controllers\Api\MediaTagController;

Route::prefix('v1/storage')->group(function () {
    //Для фронтенда сайта (без авторизации)
    Route::get('/media/public/', [MediaController::class, 'publicIndex']);

    // Административные операции (только admin|staff)
    Route::middleware(['auth:sanctum', 'role:admin|staff'])->group(function () {
        Route::get('/media/{uuid}/file', [MediaController::class, 'file']); //Оригинальный файл
        Route::post('/media/upload', [MediaController::class, 'upload']);
        Route::post('/media/download', [MediaController::class, 'download']); //Загрузка в хранилище по url
        Route::put('/media/{id}/tags', [MediaController::class, 'syncTags']);
        //Работа с медиа по id - изменение параметров сортировки, title и другие
        Route::put('/media/{id}', [MediaController::class, 'update']); //Редактировать поля медиа
        Route::delete('/media/{id}', [MediaController::class, 'destroy']); //Удаление по id
        Route::post('/media/clear-cache', [MediaController::class, 'clearCache']);
        Route::apiResource('media/tag', MediaTagController::class);

        Route::apiResource('gallery', GalleryController::class);
        Route::get('/gallery/{gallery}/media', [GalleryController::class, 'media']);
        Route::get('/gallery/{gallery}/tags', [GalleryController::class, 'tags']);
    });

    //Для загрузки изображений клиентами - фото для отзыва
    Route::middleware(['auth:sanctum', 'role:client'])->group(function () {
        Route::post('/client/media', [ClientMediaController::class, 'store']);
        Route::delete('/client/media/{uuid}', [ClientMediaController::class, 'destroy']); //? Возможно и не понадобится
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/client/media', [ClientMediaController::class, 'index']);    });

    Route::get('/media/{uuid}', [MediaController::class, 'show']);
});

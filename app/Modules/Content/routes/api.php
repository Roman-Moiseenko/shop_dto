<?php

use App\Modules\Content\Presentation\Http\Controllers\Api\PageBlockController;
use App\Modules\Content\Presentation\Http\Controllers\Api\PageController;
use App\Modules\Content\Presentation\Http\Controllers\Api\WidgetController;
use App\Modules\Content\Presentation\Http\Controllers\Api\WidgetInstanceController;

Route::prefix('v1/content')->group(function () {

    //Без доступа для клиентской части


    //С доступом для админки
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('widgets', WidgetController::class);
        Route::apiResource('widget-instances', WidgetInstanceController::class);
        Route::apiResource('page', PageController::class);
        // Дополнительный маршрут для жёсткого удаления
        Route::delete('/page/{id}/force', [PageController::class, 'forceDestroy']);
        Route::patch('/page/{id}/restore', [PageController::class, 'restore']);

        // Блоки страниц
        Route::prefix('page/{page}/block')->group(function () {
            Route::post('/', [PageBlockController::class, 'addBlock']);
            Route::delete('/{block}', [PageBlockController::class, 'removeBlock']);
            Route::put('/sort', [PageBlockController::class, 'updateSort']);
            Route::patch('/caption', [PageBlockController::class, 'updateCaption']);
        });
    });
});

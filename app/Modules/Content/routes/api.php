<?php

use App\Modules\Content\Presentation\Http\Controllers\Api\PageBlockController;
use App\Modules\Content\Presentation\Http\Controllers\Api\PageController;
use App\Modules\Content\Presentation\Http\Controllers\Api\PublicPageController;
use App\Modules\Content\Presentation\Http\Controllers\Api\WidgetController;
use App\Modules\Content\Presentation\Http\Controllers\Api\WidgetInstanceController;

Route::prefix('v1/content')->group(function () {



    //С доступом для админки
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('widget/options', [WidgetController::class, 'options']);
        Route::apiResource('widget', WidgetController::class);
        Route::apiResource('widget-instance', WidgetInstanceController::class);
        Route::apiResource('page', PageController::class);
        // Дополнительный маршрут для жёсткого удаления
        Route::delete('/page/{id}/force', [PageController::class, 'forceDestroy']);
        Route::patch('/page/{id}/restore', [PageController::class, 'restore']);
        Route::post('page/{id}/publish', [PageController::class, 'publish']);
        Route::post('page/{id}/unpublish', [PageController::class, 'unpublish']);
        // Блоки страниц
        Route::prefix('page/{page}/block')->group(function () {
            Route::post('/', [PageBlockController::class, 'addBlock']);
            Route::delete('/{block}', [PageBlockController::class, 'removeBlock']);
            Route::put('/sort', [PageBlockController::class, 'updateSort']);
            Route::patch('/caption', [PageBlockController::class, 'updateCaption']);
        });
    });
});

//Без доступа для клиентской части
Route::prefix('v1/public')->group(function () {
    Route::get('/page/home', [PublicPageController::class, 'home']);
    Route::get('/page/{slug}', [PublicPageController::class, 'show']);
});

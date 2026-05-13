<?php

use App\Modules\Content\Presentation\Http\Controllers\Api\ContactController;
use App\Modules\Content\Presentation\Http\Controllers\Api\MenuController;
use App\Modules\Content\Presentation\Http\Controllers\Api\MenuItemController;
use App\Modules\Content\Presentation\Http\Controllers\Api\PageBlockController;
use App\Modules\Content\Presentation\Http\Controllers\Api\PageController;
use App\Modules\Content\Presentation\Http\Controllers\Api\PublicPageController;
use App\Modules\Content\Presentation\Http\Controllers\Api\SiteSettingsController;
use App\Modules\Content\Presentation\Http\Controllers\Api\WidgetController;
use App\Modules\Content\Presentation\Http\Controllers\Api\WidgetInstanceController;

Route::prefix('v1/content')->group(function () {



    //С доступом для админки
    Route::middleware(['auth:sanctum', 'role:admin|staff'])->group(function () {
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
        //Меню
        Route::apiResource('menu', MenuController::class);
        // Пункты меню
        Route::prefix('menu/{menu}/item')->group(function () {
            Route::get('/', [MenuItemController::class, 'index']);           // список пунктов
            Route::post('/', [MenuItemController::class, 'store']);          // создать
            Route::get('/{item}', [MenuItemController::class, 'show'])->whereNumber('item');      // просмотр
            Route::put('/reorder', [MenuItemController::class, 'reorder']);   // изменить порядок (принимает id и newSort)
            Route::put('/{item}', [MenuItemController::class, 'update'])->whereNumber('item');    // редактировать
            Route::delete('/{item}', [MenuItemController::class, 'destroy']); // удалить
            Route::put('/{item}/parent', [MenuItemController::class, 'changeParent']); // сменить родителя

            Route::post('/{item}/activate', [MenuItemController::class, 'activate']);
            Route::post('/{item}/deactivate', [MenuItemController::class, 'deactivate']);
        });

        Route::prefix('site')->group(function () {
            Route::get('header', [SiteSettingsController::class, 'getHeader']);
            Route::put('header', [SiteSettingsController::class, 'updateHeader']);
            Route::get('footer', [SiteSettingsController::class, 'getFooter']);
            Route::put('footer', [SiteSettingsController::class, 'updateFooter']);
        });
        Route::prefix('contact')->group(function () {
            Route::get('/', [ContactController::class, 'index']);
            Route::post('/', [ContactController::class, 'store']);
            Route::get('/{id}', [ContactController::class, 'show']);
            Route::put('/{id}', [ContactController::class, 'update']);
            Route::delete('/{id}', [ContactController::class, 'destroy']);

            Route::put('/{id}/activate', [ContactController::class, 'activate']);
            Route::put('/{id}/deactivate', [ContactController::class, 'deactivate']);
            Route::put('/{id}/sort', [ContactController::class, 'reorder']);
            Route::get('/types', [ContactController::class, 'types']);
        });
    });
});

//Без доступа для клиентской части
Route::prefix('v1/public')->group(function () {
    Route::get('/page/home', [PublicPageController::class, 'home']);
    Route::get('/page/{slug}', [PublicPageController::class, 'show']);
});

<?php

Route::prefix('v1/content')->group(function () {

    //Без доступа для клиентской части


    //С доступом для админки
    Route::middleware('auth:sanctum')->group(function () {

    });
});

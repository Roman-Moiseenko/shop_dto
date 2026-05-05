<?php

// use Illuminate\Support\Facades\Route;

// Route::middleware([])->prefix('storage')->group(function () {

//     Route::get('/api', function () {
//         return 'storage';
//     });

// });


use App\Modules\Storage\Presentation\Http\Controllers\Api\MediaController;

Route::prefix('v1/storage')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/upload', [MediaController::class, 'upload']);
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/{uuid}', [MediaController::class, 'show']);
    Route::delete('/media/{uuid}', [MediaController::class, 'destroy']);
});

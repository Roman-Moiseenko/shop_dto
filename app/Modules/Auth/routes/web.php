<?php

// use Illuminate\Support\Facades\Route;

// Route::middleware([])->prefix('auth')->group(function () {

//     Route::get('/', function () {
//         return 'auth';
//     });

// });
use App\Modules\Auth\Presentation\Http\Controllers\Api\ClientController;

Route::get('/verify-email', [ClientController::class, 'verifyEmail'])->name('verify-email');

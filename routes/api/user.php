<?php

use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('current/user', [UserController::class, 'getCurrentUser']);

    Route::get('user', [UserController::class, 'index'])->middleware('role:admin');
    Route::get('user/count/all', [UserController::class, 'getUserCount'])->middleware('role:admin');
    Route::get('user/count/today', [UserController::class, 'getUserSignToday'])->middleware('role:admin');
    Route::put('user/update/verified/{user}', [UserController::class, 'updateUserVerification'])->middleware('role:admin');

    Route::post('user', [UserController::class, 'store']);
    Route::get('user/{user}', [UserController::class, 'show']);
    Route::put('user/{user}', [UserController::class, 'update'])->middleware('throttle:upload');
    Route::delete('user/{user}', [UserController::class, 'destroy']);
});

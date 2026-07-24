<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'authLogin'])->middleware('throttle:login');
Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::post('auth/resend-verification', [AuthController::class, 'sendVerification'])->middleware('throttle:password-reset');
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'authLogout']);
});

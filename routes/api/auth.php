<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'authLogin'])->middleware('throttle:login');
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/resend-verification', [AuthController::class, 'sendVerification']);
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'authLogout']);
});

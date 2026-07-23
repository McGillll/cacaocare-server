<?php

use App\Http\Controllers\Api\DownloadLink\DownloadLinksController;
use Illuminate\Support\Facades\Route;

Route::get('download/latest', [DownloadLinksController::class, 'getLatest']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('download/latest/date', [DownloadLinksController::class, 'getLatestDate']);
    Route::get('download_link', [DownloadLinksController::class, 'index']);
    Route::get('download_link/{download_link}', [DownloadLinksController::class, 'show']);

    Route::middleware('role:admin')->group(function () {
        Route::post('download_link', [DownloadLinksController::class, 'store']);
        Route::put('download_link/{download_link}', [DownloadLinksController::class, 'update']);
        Route::delete('download_link/{download_link}', [DownloadLinksController::class, 'destroy']);
    });
});

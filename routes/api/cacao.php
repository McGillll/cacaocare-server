<?php

use App\Http\Controllers\Api\Cacao\CacaoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('cacao', [CacaoController::class, 'store'])->middleware('throttle:upload');

    Route::get('cacao/feed/{order}/{filter}', [CacaoController::class, 'feed']);
    Route::get('cacao/recent/upload', [CacaoController::class, 'getRecent']);
    Route::get('cacao/status/count', [CacaoController::class, 'getStatusCount']);
    Route::get('cacao/upload/today', [CacaoController::class, 'getUploadedTodayCount']);
    Route::get('cacao/upload/years/option', [CacaoController::class, 'uploadYears']);

    Route::get('cacao/graph/yearly', [CacaoController::class, 'getCacaoYearGraph']);
    Route::get('cacao/upload/trend', [CacaoController::class, 'getUploadCountByDate']);

    Route::get('cacao/heatmap/{filter}', [CacaoController::class, 'getWeeklyHeatMapData']);
    Route::get('cacao/heatmap/{filter}/{date}', [CacaoController::class, 'getCacaoHeatMapData']);

    Route::get('cacao/region/alert/{date}', [CacaoController::class, 'regionAlertData']);

    Route::get('cacao/upload/count/{id}', [CacaoController::class, 'getUploadCountByUser']);
    Route::get('cacao/user/upload/{id}', [CacaoController::class, 'getCacaoUploadedByUser']);
    Route::get('cacao/disease/weeks', [CacaoController::class, 'getHighestDiseaseWithinTheWeek']);

    Route::get('cacao/{cacao}', [CacaoController::class, 'show']);
    Route::put('cacao/{cacao}', [CacaoController::class, 'update'])->middleware('throttle:upload');
    Route::delete('cacao/{cacao}', [CacaoController::class, 'destroy']);
});

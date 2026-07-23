<?php

use App\Http\Controllers\CacaoController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DownloadLinksController;
use App\Http\Controllers\TreatmentController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/api/auth.php';
require __DIR__.'/api/user.php';

Route::get("download/latest", [DownloadLinksController::class, "getLatest"]);
Route::get("treatment", [TreatmentController::class, "index"])->name(
    "treatment.index",
);

Route::middleware(["auth:sanctum"])->group(function () {
    Route::apiResources([
        "cacao" => CacaoController::class,
        "download_link" => DownloadLinksController::class,
    ]);

    // Comments
    Route::get("cacaos/{cacao}/comments", [CommentController::class, "index"]);
    Route::post("cacaos/{cacao}/comments", [CommentController::class, "store"]);
    Route::put("comments/{comment}", [CommentController::class, "update"]);
    Route::delete("comments/{comment}", [CommentController::class, "destroy"]);

    //DownloadLinks
    Route::get("download/latest/date", [DownloadLinksController::class, "getLatestDate"]);

    //Cacao
    Route::get("cacao/recent/upload", [CacaoController::class, "getRecent"]);
    Route::get("cacao/status/count", [CacaoController::class,"getStatusCount"]);
    Route::get("cacao/feed/{order}/{filter}", [CacaoController::class,"index"]);
    Route::get("cacao/upload/today", [CacaoController::class,"getUploadedTodayCount"]);
    Route::get("cacao/upload/years/option", [CacaoController::class,"uploadYears"]);

    //Graph
    Route::get("cacao/graph/yearly", [CacaoController::class,"getCacaoYearGraph"]);
    Route::get("cacao/upload/trend", [CacaoController::class,"getUploadCountByDate"]);

    //Heatmap
    Route::get("cacao/heatmap/{filter}", [CacaoController::class,"getWeeklyHeatMapData"]);
    Route::get("cacao/heatmap/{filter}/{date}", [CacaoController::class,"getCacaoHeatMapData"]);

    //Region Alert Table
    Route::get("cacao/region/alert/{date}", [CacaoController::class,"regionAlertData"]);

    Route::get("cacao/upload/count/{id}", [CacaoController::class,"getUploadCountByUser"]);
    Route::get("cacao/user/upload/{id}", [CacaoController::class,"getCacaoUploadedByUser"]);
    Route::get("cacao/disease/weeks", [CacaoController::class,"getHighestDiseaseWithinTheWeek"]);

    // Treatment routes
    Route::post("treatment", [TreatmentController::class, "store"])->name(
        "treatment.store",
    );
    Route::get("treatment/{treatment}", [
        TreatmentController::class,
        "show",
    ])->name("treatment.show");
    Route::put("treatment/{treatment}", [
        TreatmentController::class,
        "update",
    ])->name("treatment.update");
    Route::delete("treatment/{treatment}", [
        TreatmentController::class,
        "destroy",
    ])->name("treatment.destroy");
});

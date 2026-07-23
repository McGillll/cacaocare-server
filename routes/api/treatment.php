<?php

use App\Http\Controllers\Api\Treatment\TreatmentController;
use Illuminate\Support\Facades\Route;

Route::get('treatment', [TreatmentController::class, 'index'])->name('treatment.index');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('treatment/{treatment}', [TreatmentController::class, 'show'])->name('treatment.show');

    Route::middleware('role:admin')->group(function () {
        Route::post('treatment', [TreatmentController::class, 'store'])->name('treatment.store');
        Route::put('treatment/{treatment}', [TreatmentController::class, 'update'])->name('treatment.update');
        Route::delete('treatment/{treatment}', [TreatmentController::class, 'destroy'])->name('treatment.destroy');
    });
});

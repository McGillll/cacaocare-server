<?php

use App\Http\Controllers\Api\Comment\CommentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('cacaos/{cacao}/comments', [CommentController::class, 'index']);
    Route::post('cacaos/{cacao}/comments', [CommentController::class, 'store']);
    Route::put('comments/{comment:uuid}', [CommentController::class, 'update']);
    Route::delete('comments/{comment:uuid}', [CommentController::class, 'destroy']);
});

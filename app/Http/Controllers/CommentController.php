<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCommentRequest;
use App\Models\Cacao;
use App\Models\Comment;
use App\Services\CommentService;
use App\Http\Requests\StoreCommentRequest;

class CommentController
{
    public function index(Cacao $cacao, CommentService $commentService)
    {
        $comments = $commentService->getComments($cacao);
        return response()->json($comments);
    }

    public function store(
        StoreCommentRequest $request,
        Cacao $cacao,
        CommentService $commentService,
    ) {
        $comment = $commentService->createComment($request, $cacao);
        return response()->json($comment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateCommentRequest $request,
        int $id,
        CommentService $commentService,
    ) {
        $comment = $commentService->updateComment($request, $id);
        return response()->json([
            'data' => $comment
        ],200);
    }

    public function destroy(int $id, CommentService $commentService)
    {
        $commentService->deleteComment($id);
        return response()->json(null, 204);
    }
}

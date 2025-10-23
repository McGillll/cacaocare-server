<?php

namespace App\Http\Controllers;

use App\Models\Cacao;
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
        Comment $comment,
        CommentService $commentService,
    ) {
        $this->authorize("update", $comment);
        $comment = $commentService->updateComment($request, $comment);
        return response()->json($comment);
    }

    public function destroy(Comment $comment, CommentService $commentService)
    {
        $this->authorize("delete", $comment);
        $commentService->deleteComment($comment);
        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api\Comment;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Interfaces\Comment\CommentServiceInterface;
use App\Models\Cacao;
use App\Models\Comment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CommentController
{
    use ApiResponse;

    public function __construct(private CommentServiceInterface $commentService)
    {
    }

    public function index(Cacao $cacao)
    {
        $comments = $this->commentService->paginateForCacao($cacao);

        return $this->success($comments->through(fn ($comment) => new CommentResource($comment)));
    }

    public function store(StoreCommentRequest $request, Cacao $cacao)
    {
        $payload = (object) $request->validated();
        $comment = $this->commentService->create($request->user(), $cacao, $payload);

        return $this->success(new CommentResource($comment), 'Comment created successfully.', 201);
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $payload = (object) $request->validated();
        $updated = $this->commentService->update($request->user(), $comment, $payload);

        return $this->success(new CommentResource($updated), 'Comment updated successfully.');
    }

    public function destroy(Request $request, Comment $comment)
    {
        $this->commentService->delete($request->user(), $comment);

        return $this->success(null, 'Comment deleted successfully.');
    }
}

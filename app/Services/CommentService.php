<?php

namespace App\Services;

use App\Http\Controllers\UserController;
use App\Models\Cacao;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentService
{
    public function getComments(Cacao $cacao)
    {
        return $cacao->comments()->with('user:id,username,profile')->latest()->paginate(10);
    }

    public function createComment(Request $request, Cacao $cacao): Comment
    {
        $request->user_id = auth()->id();
        $comment = $cacao->comments()->create([
            'user_id' => $request->user_id,
            'body' => $request->body,
        ]);

        $comment->load('user:id,username,profile');

        return $comment;
    }

    public function updateComment(Request $request, int $id): Comment
    {
        $comment = Comment::where('id' , $id)->with('user')->firstOrFail();
        $comment->body = ($request->body);
        $comment->save();
        return $comment;
    }

    public function deleteComment(int $id): void
    {
        $comment = Comment::where('id' , $id)->firstOrFail();
        $comment->delete();
    }
}

<?php

namespace App\Repositories;

use App\Interfaces\Comment\CommentRepositoryInterface;
use App\Models\Cacao;
use App\Models\Comment;

class CommentRepository implements CommentRepositoryInterface
{
    public function paginateForCacao(Cacao $cacao)
    {
        return $cacao->comments()->with('user:id,username,profile')->latest()->paginate(10);
    }

    public function create(Cacao $cacao, object $payload)
    {
        return $cacao->comments()->create([
            'user_id' => $payload->user_id,
            'body' => $payload->body,
        ])->load('user:id,username,profile');
    }

    public function update(Comment $comment, object $payload)
    {
        $comment->body = $payload->body;
        $comment->save();

        return $comment->load('user:id,username,profile');
    }

    public function delete(Comment $comment)
    {
        return $comment->delete();
    }
}

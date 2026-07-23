<?php

namespace App\Interfaces\Comment;

use App\Models\Cacao;
use App\Models\Comment;

interface CommentRepositoryInterface
{
    public function paginateForCacao(Cacao $cacao);

    public function create(Cacao $cacao, object $payload);

    public function update(Comment $comment, object $payload);

    public function delete(Comment $comment);
}

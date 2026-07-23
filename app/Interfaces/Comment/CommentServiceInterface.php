<?php

namespace App\Interfaces\Comment;

use App\Models\Cacao;
use App\Models\Comment;
use App\Models\User;

interface CommentServiceInterface
{
    public function paginateForCacao(Cacao $cacao);

    public function create(User $authUser, Cacao $cacao, object $payload);

    public function update(User $authUser, Comment $comment, object $payload);

    public function delete(User $authUser, Comment $comment);
}

<?php

namespace App\Services\Comment;

use App\Exceptions\UnauthorizedActionException;
use App\Interfaces\Comment\CommentRepositoryInterface;
use App\Interfaces\Comment\CommentServiceInterface;
use App\Models\Cacao;
use App\Models\Comment;
use App\Models\User;

class CommentService implements CommentServiceInterface
{
    public function __construct(private CommentRepositoryInterface $commentRepository)
    {
    }

    public function paginateForCacao(Cacao $cacao)
    {
        return $this->commentRepository->paginateForCacao($cacao);
    }

    public function create(User $authUser, Cacao $cacao, object $payload)
    {
        $payload->user_id = $authUser->id;

        return $this->commentRepository->create($cacao, $payload);
    }

    public function update(User $authUser, Comment $comment, object $payload)
    {
        $this->assertOwnerOrAdmin($authUser, $comment);

        return $this->commentRepository->update($comment, $payload);
    }

    public function delete(User $authUser, Comment $comment)
    {
        $this->assertOwnerOrAdmin($authUser, $comment);

        return $this->commentRepository->delete($comment);
    }

    private function assertOwnerOrAdmin(User $authUser, Comment $comment)
    {
        if ($authUser->id !== $comment->user_id && $authUser->role !== 'admin') {
            throw new UnauthorizedActionException('You may only modify your own comment.');
        }
    }
}

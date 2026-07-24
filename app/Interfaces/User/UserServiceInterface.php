<?php

namespace App\Interfaces\User;

use App\Models\User;

interface UserServiceInterface
{
    public function all();

    public function create(object $payload);

    public function update(User $authUser, User $target, object $payload);

    public function delete(User $authUser, User $target);

    public function trashed();

    public function restore(User $target);

    public function toggleVerification(User $target);

    public function countAll();

    public function countCreatedToday();
}

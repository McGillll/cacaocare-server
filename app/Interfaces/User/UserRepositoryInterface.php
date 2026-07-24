<?php

namespace App\Interfaces\User;

use App\Models\User;

interface UserRepositoryInterface
{
    public function all();

    public function findByEmail(object $payload);

    public function create(object $payload);

    public function update(User $user, object $payload);

    public function delete(User $user);

    public function trashed();

    public function restore(User $user);

    public function countByRole(string $role);

    public function countCreatedToday(string $role);
}

<?php

namespace App\Services\User;

use App\Exceptions\UnauthorizedActionException;
use App\Interfaces\User\UserRepositoryInterface;
use App\Interfaces\User\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserService implements UserServiceInterface
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function all()
    {
        return $this->userRepository->all();
    }

    public function create(object $payload)
    {
        return $this->userRepository->create($payload);
    }

    public function update(User $authUser, User $target, object $payload)
    {
        $this->assertOwnerOrAdmin($authUser, $target);

        if (!empty($payload->profileFile)) {
            $filename = $target->uuid.'_'.$payload->profileFile->getClientOriginalName();
            $path = $payload->profileFile->storeAs('profile-photos/'.$target->email, $filename);
            $payload->profile = 'https://cacaocare.s3.ap-southeast-2.amazonaws.com/'.$path;
        }

        return $this->userRepository->update($target, $payload);
    }

    public function delete(User $authUser, User $target)
    {
        $this->assertOwnerOrAdmin($authUser, $target);

        return $this->userRepository->delete($target);
    }

    public function toggleVerification(User $target)
    {
        $target->email_verified_at = $target->email_verified_at ? null : Carbon::now();
        $target->save();

        return $target;
    }

    public function countAll()
    {
        return $this->userRepository->countByRole('user');
    }

    public function countCreatedToday()
    {
        return $this->userRepository->countCreatedToday('user');
    }

    private function assertOwnerOrAdmin(User $authUser, User $target)
    {
        if ($authUser->uuid !== $target->uuid && $authUser->role !== 'admin') {
            throw new UnauthorizedActionException('You may only modify your own account.');
        }
    }
}

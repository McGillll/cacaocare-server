<?php

namespace App\Services\User;

use App\Exceptions\UnauthorizedActionException;
use App\Interfaces\User\UserRepositoryInterface;
use App\Interfaces\User\UserServiceInterface;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class UserService implements UserServiceInterface
{
    private const DEFAULT_PROFILE_PATH = 'profile-photos/default_profile.png';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ImageUploadService $imageUploadService,
    ) {
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

        $oldProfile = $target->profile;

        if (!empty($payload->profileFile)) {
            $filename = $target->uuid.'_'.$payload->profileFile->getClientOriginalName();
            $path = $this->imageUploadService->storeAsWebp($payload->profileFile, 'profile-photos/'.$target->email, $filename);
            $payload->profile = Storage::disk('r2')->url($path);
        }

        $updated = $this->userRepository->update($target, $payload);

        if (!empty($payload->profileFile) && $oldProfile !== Storage::disk('r2')->url(self::DEFAULT_PROFILE_PATH)) {
            $this->imageUploadService->deleteFromUrl($oldProfile);
        }

        return $updated;
    }

    public function delete(User $authUser, User $target)
    {
        $this->assertOwnerOrAdmin($authUser, $target);

        // Soft delete only — the profile image is left in place in case the account is restored.
        $deleted = $this->userRepository->delete($target);

        // Revoke existing tokens so a soft-deleted user can't keep using an already-issued session.
        $target->tokens()->delete();

        return $deleted;
    }

    public function trashed()
    {
        return $this->userRepository->trashed();
    }

    public function restore(User $target)
    {
        return $this->userRepository->restore($target);
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

<?php

namespace App\Repositories;

use App\Interfaces\User\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserRepository implements UserRepositoryInterface
{
    public function all()
    {
        return User::where('role', 'user')->get();
    }

    public function findByEmail(object $payload)
    {
        return User::where('email', $payload->email)->first();
    }

    public function create(object $payload)
    {
        $user = new User();
        $user->username = $payload->username;
        $user->email = $payload->email;
        $user->password = Hash::make($payload->password);
        $user->profile = Storage::disk('r2')->url('profile-photos/default_profile.png');
        $user->region = $payload->region ?? null;
        $user->province = $payload->province ?? null;
        $user->city = $payload->city ?? null;
        $user->barangay = $payload->barangay;
        $user->role = 'user';
        $user->save();

        return $user;
    }

    public function update(User $user, object $payload)
    {
        if (!empty($payload->username)) {
            $user->username = $payload->username;
        }
        if (!empty($payload->password)) {
            $user->password = Hash::make($payload->password);
        }
        if (!empty($payload->region)) {
            $user->region = $payload->region;
        }
        if (!empty($payload->province)) {
            $user->province = $payload->province;
        }
        if (!empty($payload->city)) {
            $user->city = $payload->city;
        }
        if (!empty($payload->barangay)) {
            $user->barangay = $payload->barangay;
        }
        if (!empty($payload->profile)) {
            $user->profile = $payload->profile;
        }
        $user->save();

        return $user;
    }

    public function delete(User $user)
    {
        return $user->delete();
    }

    public function trashed()
    {
        return User::onlyTrashed()->where('role', 'user')->latest('deleted_at')->get();
    }

    public function restore(User $user)
    {
        $user->restore();

        return $user;
    }

    public function countByRole(string $role)
    {
        return User::where('role', $role)->count();
    }

    public function countCreatedToday(string $role)
    {
        return User::where('role', $role)
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }
}

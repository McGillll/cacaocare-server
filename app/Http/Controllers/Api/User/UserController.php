<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Interfaces\User\UserServiceInterface;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UserController
{
    use ApiResponse;

    public function __construct(private UserServiceInterface $userService)
    {
    }

    public function index()
    {
        $users = $this->userService->all();

        return $this->success(UserResource::collection($users));
    }

    public function store(UserStoreRequest $request)
    {
        $payload = (object) $request->validated();
        $user = $this->userService->create($payload);

        return $this->success(new UserResource($user), 'User created successfully.', 201);
    }

    public function show(User $user)
    {
        return $this->success(new UserResource($user));
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $payload = (object) $request->validated();
        if ($request->hasFile('profile')) {
            $payload->profileFile = $request->file('profile');
        }

        $updated = $this->userService->update($request->user(), $user, $payload);

        return $this->success(new UserResource($updated));
    }

    public function destroy(Request $request, User $user)
    {
        $this->userService->delete($request->user(), $user);

        return $this->success(null, 'User deleted successfully.');
    }

    public function getCurrentUser(Request $request)
    {
        return $this->success(new UserResource($request->user()));
    }

    public function getUserCount()
    {
        return $this->success($this->userService->countAll());
    }

    public function getUserSignToday()
    {
        return $this->success($this->userService->countCreatedToday());
    }

    public function updateUserVerification(User $user)
    {
        $updated = $this->userService->toggleVerification($user);

        return $this->success(new UserResource($updated));
    }
}

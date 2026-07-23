<?php

namespace App\Interfaces\Auth;

use App\Models\User;

interface AuthServiceInterface
{
    public function login(object $payload);

    public function logout(User $user);

    public function register(object $payload);

    public function resendVerification(object $payload);

    public function verifyEmail(string $token);

    public function forgotPassword(object $payload);

    public function resetPassword(object $payload);
}

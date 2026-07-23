<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\NotFoundException;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Resources\UserResource;
use App\Interfaces\Auth\AuthServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuthController
{
    use ApiResponse;

    public function __construct(private AuthServiceInterface $authService)
    {
    }

    public function authLogin(LoginRequest $request)
    {
        $payload = (object) $request->validated();
        $result = $this->authService->login($payload);

        return $this->success(new UserResource($result['user']), null, 200, ['token' => $result['token']]);
    }

    public function authLogout(Request $request)
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Successfully logged out.');
    }

    public function register(UserStoreRequest $request)
    {
        $payload = (object) $request->validated();
        $this->authService->register($payload);

        return $this->success(null, 'Verification email sent.');
    }

    public function sendVerification(ResendVerificationRequest $request)
    {
        $payload = (object) $request->validated();
        $this->authService->resendVerification($payload);

        return $this->success(null, 'Verification email sent!');
    }

    public function verifyEmail(Request $request)
    {
        try {
            $this->authService->verifyEmail($request->query('token'));

            return redirect()->to('https://cacao-care.nuxt.dev/email-verified');
        } catch (NotFoundException $e) {
            return redirect()->to('https://cacao-care.nuxt.dev/verify-failed');
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $payload = (object) $request->validated();
        $this->authService->forgotPassword($payload);

        return $this->success(null, 'If this email is registered, a password reset link has been sent.');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $payload = (object) $request->validated();
        $this->authService->resetPassword($payload);

        return $this->success(null, 'Password has been successfully reset.');
    }
}

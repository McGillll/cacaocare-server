<?php

namespace App\Services\Auth;

use App\Exceptions\InvalidPayloadException;
use App\Exceptions\NotFoundException;
use App\Interfaces\Auth\AuthServiceInterface;
use App\Interfaces\User\UserRepositoryInterface;
use App\Mail\PasswordResetMail;
use App\Mail\VerificationEmail;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService implements AuthServiceInterface
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function login(object $payload)
    {
        $user = $this->userRepository->findByEmail($payload);

        if (!$user || !Hash::check($payload->password, $user->password)) {
            throw new AuthenticationException('These credentials do not match our records.');
        }

        $token = $user->createToken(
            'auth-token',
            [$user->role],
            Carbon::now()->addDays(30),
        )->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user)
    {
        $user->currentAccessToken()->delete();
    }

    public function register(object $payload)
    {
        return DB::transaction(function () use ($payload) {
            $user = $this->userRepository->create($payload);

            $this->issueVerificationToken($user->email);

            return $user;
        });
    }

    public function resendVerification(object $payload)
    {
        $this->issueVerificationToken($payload->email);
    }

    public function verifyEmail(string $token)
    {
        $record = DB::table('email_verifications')->where('token', $token)->first();

        if (!$record) {
            throw new NotFoundException('Invalid or expired verification token.');
        }

        DB::table('users')->where('email', $record->email)->update(['email_verified_at' => Carbon::now()]);
        DB::table('email_verifications')->where('token', $token)->delete();
    }

    public function forgotPassword(object $payload)
    {
        $user = $this->userRepository->findByEmail($payload);

        if (!$user) {
            throw new NotFoundException('This email is not registered.');
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => Carbon::now()],
        );

        $resetUrl = 'https://cacao-care.nuxt.dev/reset-password?token='.$token.'&email='.urlencode($user->email);
        Mail::to($user->email)->send(new PasswordResetMail($resetUrl));
    }

    public function resetPassword(object $payload)
    {
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $payload->email)
            ->where('token', $payload->token)
            ->first();

        if (!$tokenData) {
            throw new NotFoundException('Invalid token or email.');
        }

        if (Carbon::parse($tokenData->created_at)->addHour()->isPast()) {
            DB::table('password_reset_tokens')->where('email', $payload->email)->delete();
            throw new InvalidPayloadException('Token has expired.');
        }

        $user = $this->userRepository->findByEmail($payload);
        if (!$user) {
            throw new NotFoundException('User not found.');
        }

        $user->password = Hash::make($payload->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $payload->email)->delete();
    }

    private function issueVerificationToken(string $email)
    {
        $token = Str::random(40);

        DB::table('email_verifications')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => now()],
        );

        $url = url('/verify-email?token='.$token);
        Mail::to($email)->send(new VerificationEmail($url));
    }
}

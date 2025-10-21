<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Mail\PasswordResetMail;
use App\Mail\VerificationEmail;
use App\Models\EmailVerification;
use App\Models\User;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController
{
    public function register(UserStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            // Create user
            UserController::store($request);

            // Generate token
            $token = Str::random(40);

            // Insert email verification
            DB::table("email_verifications")->insert([
                "email" => $request->email,
                "token" => $token,
                "created_at" => now(),
            ]);

            // Send verification email
            $url = url("/verify-email?token=" . $token);
            Mail::to($request->email)->send(new VerificationEmail($url));

            DB::commit();

            return response()->json(
                [
                    "message" => "Verification email sent",
                    "status" => 200,
                ],
                200,
            );
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging

            return response()->json(
                [
                    "message" => "Registration failed",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function sendVerification(Request $request)
    {
        $token = Str::random(40);

        DB::table("email_verifications")->updateOrInsert(
            ["email" => $request->email], // Lookup condition
            [
                "token" => $token,
                "created_at" => now(),
            ],
        );

        $url = url("/verify-email?token=" . $token);
        Mail::to($request->email)->send(new VerificationEmail($url));

        return response()->json(["data" => "Verification email sent!"], 200);
    }

    public function verifyEmail(Request $request)
    {
        $token = $request->query("token");
        $record = DB::table("email_verifications")
            ->where("token", $token)
            ->first();

        if ($record) {
            DB::table("users")
                ->where("email", $record->email)
                ->update(["email_verified_at" => Carbon::now()]);
            DB::table("email_verifications")->where("token", $token)->delete();

            // Redirect to email-verified page
            return redirect()->to("https://cacao-care.nuxt.dev/email-verified");
        }
        // Redirect to verify-failed page if token is invalid
        return redirect()->to("https://cacao-care.nuxt.dev/verify-failed");
    }

    public function authLogin(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        $user = User::where("email", $request->email)->first();

        if (!$user) {
            return response()->json(
                [
                    "message" => "User  not found",
                    "errors" => [
                        "email" => "This email is not registered",
                    ],
                ],
                404,
            );
        }

        if (!FacadesHash::check($request->password, $user->password)) {
            return response()->json(
                [
                    "message" => "Invalid Password",
                    "errors" => [
                        "password" => "Invalid password",
                    ],
                ],
                400,
            );
        }

        $token = $user->createToken(
            "auth-token",
            ["*"],
            Carbon::now()->addDays(30),
        )->plainTextToken;
        return response()->json(
            [
                "token" => $token,
                "data" => $user,
            ],
            200,
        );
    }

    public function authLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["message", "Successfully Logout"], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(["email" => "required|email"]);

        $user = User::where("email", $request->email)->first();

        if (!$user) {
            return response()->json(
                [
                    "message" => "This email is not registered.",
                    "status" => 404,
                ],
                404,
            );
        }

        // Create a new token
        $token = Str::random(60);

        // Save the token
        $result = DB::table("password_reset_tokens")->updateOrInsert(
            ["email" => $user->email],
            [
                "token" => $token,
                "created_at" => Carbon::now(),
            ],
        );

        // Send the email
        // IMPORTANT: Ensure you have a Mailable created for this.
        // e.g., php artisan make:mail PasswordResetMail
        $resetUrl =
            // "http://localhost:3000/reset-password?token=" .
            "https://cacao-care.nuxt.dev/reset-password?token=" .
            $token .
            "&email=" .
            urlencode($user->email);

        // You would typically use a Mailable here
        Mail::to($user->email)->send(new PasswordResetMail($resetUrl));

        return response()->json(
            [
                "message" =>
                    "If this email is registered, a password reset link has been sent.",
                "status" => 200,
                "data" => $result
            ],
            200,
        );
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "token" => "required|string",
            "password" => "required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*_]).+$/",
        ]);

        // Find the password reset token
        $tokenData = DB::table("password_reset_tokens")
            ->where("email", $request->email)
            ->where("token", $request->token)
            ->first();


        // Check if the token is valid
        if (!$tokenData) {
            return response()->json(
                ["message" => "Invalid token or email.",
                "data" => $request->token
            ],
                404,

            );
        }

        // Check if the token has expired (optional, e.g., 1 hour)
        if (Carbon::parse($tokenData->created_at)->addHour()->isPast()) {
            // Token expired, delete it
            DB::table("password_reset_tokens")
                ->where("email", $request->email)
                ->delete();
            return response()->json(["message" => "Token has expired."], 400);
        }

        // Find the user
        $user = User::where("email", $request->email)->first();
        if (!$user) {
            return response()->json(["message" => "User not found."], 404);
        }

        // Update the password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the password reset token
        DB::table("password_reset_tokens")
            ->where("email", $request->email)
            ->delete();

        return response()->json(
            ["message" => "Password has been successfully reset."],
            200,
        );
    }
}

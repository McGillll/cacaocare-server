<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
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
            DB::table('email_verifications')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now(),
            ]);

            // Send verification email
            $url = url('/verify-email?token=' . $token);
            Mail::to($request->email)->queue(new VerificationEmail($url));

            DB::commit();

            return response()->json([
                'message' => 'Verification email sent',
                'status' => 200
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendVerification(Request $request) {
        $token = Str::random(40);

        DB::table('email_verifications')->updateOrInsert(
            ['email' => $request->email], // Lookup condition
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        $url = url('/verify-email?token=' . $token);
        Mail::to($request->email)->queue(new VerificationEmail($url));

        return response()->json(['data' => 'Verification email sent!'], 200);
    }

    public function verifyEmail(Request $request) {
        $token = $request->query('token');
        $record = DB::table('email_verifications')->where('token', $token)->first();

        if ($record) {
            DB::table('users')
                ->where('email', $record->email)
                ->update(['email_verified_at' => Carbon::now()]);
            DB::table('email_verifications')->where('token', $token)->delete();

            // Redirect to email-verified page
            return redirect()->to('https://cacao-care.nuxt.dev/email-verified');
        }
        // Redirect to verify-failed page if token is invalid
        return redirect()->to('https://cacao-care.nuxt.dev/verify-failed');
    }

    public function authLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User  not found',
                'errors' => [
                    'email' => 'This email is not registered'
                ]
            ], 404);
        }

        if (!FacadesHash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid Password',
                'errors' => [
                    'password' => 'Invalid password'
                ]
            ], 400);
        }

        $token = $user->createToken('auth-token', ['*'], Carbon::now()->addDays(30))->plainTextToken;
        return response()->json([
            'token' => $token,
            'data' => $user
        ], 200);
    }

    public function authLogout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message', 'Successfully Logout'
        ], 200);
    }
}

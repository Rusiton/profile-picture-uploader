<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\ResetPasswordRequest;
use App\Http\Requests\api\v1\UserLoginRequest;
use App\Http\Requests\api\v1\UserRegistrationRequest;
use App\Http\Resources\api\v1\UserResource;
use App\Jobs\ResendVerificationEmail;
use App\Jobs\SendVerificationEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthenticationController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', only: ['logout', 'sendVerificationNotification', 'getVerificationStatus']),
        ];
    }


    
    public function register(UserRegistrationRequest $request) {
        $user = DB::transaction(function () use ($request) {
            $user = User::create($request->validated());
            $user->picture()->create();
            
            return $user;
        });

        SendVerificationEmail::dispatch($user)->onQueue('mailer');

        $currentTimestamp = Carbon::now();
        $tempToken = $user->createToken("$user->name\\_$currentTimestamp");

        return response()->json([
            'success' => true,
            'message' => 'User was created',
            'data' => [
                'user' => new UserResource($user->load('picture')),
                'accessToken' => $tempToken->plainTextToken,
            ],
        ], 200);
    }



    public function sendVerificationNotification(Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.'
            ], 400);
        }

        ResendVerificationEmail::dispatch($request->user())->onQueue('mailer');

        return response()->json([
            'success' => true,
            'message' => 'Verification link sent!'
        ], 200);
    }



    public function verifyEmail(EmailVerificationRequest $request) {
        $request->fulfill();
            
        // Redirect to your frontend with success message
        return redirect(config('app.frontend_url'));
    }



    public function getVerificationStatus(Request $request) {
        return response()->json([
            'verified' => $request->user()->hasVerifiedEmail()
        ]);
    }



    public function login(UserLoginRequest $request) {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credentials are incorrect',
            ], 401);
        }

        $currentTimestamp = Carbon::now();
        $tempToken = $user->createToken("$request->name\\_$currentTimestamp");

        return response()->json([
            'success' => true,
            'message' => 'User was created',
            'data' => [
                'user' => new UserResource($user->load('picture')),
                'accessToken' => $tempToken->plainTextToken,
            ],
        ], 200);
    }



    public function sendPasswordResetNotification(Request $request) {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink(
            $request->only('email'),
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => false,
                'message' => 'Reset password email could not be sent',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reset password email sent',
        ], 200);
    }



    public function resetPassword(ResetPasswordRequest $request) {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password was reset successfully',
        ], 200);
    }



    public function logout(Request $request) {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out',
        ], 200);
    }
}

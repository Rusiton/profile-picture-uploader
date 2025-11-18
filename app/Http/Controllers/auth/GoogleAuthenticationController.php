<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class GoogleAuthenticationController extends Controller
{
    public function redirect() {
        return Socialite::driver("google")->redirect();
    }

    public function callback() {
        $googleUser = Socialite::driver("google")->user();

        // Check if user already exists
        if (User::where('email', $googleUser->email)->exists()) {
            return redirect()->away(config('app.frontend_url') . "/register?e=takenEmail");
        }

        $user = DB::transaction(function () use ($googleUser) {
            $user = User::updateOrCreate(
                ['google_id' => $googleUser->id],
                [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Str::password(12),
                    'email_verified_at' => now(),
                ]
            );
    
            $user->picture()->firstOrCreate();

            return $user;
        });

        $token = $user->createToken('google-auth')->plainTextToken;

        return redirect()->away(config('app.frontend_url') . "/auth/callback?token={$token}");
    }
}

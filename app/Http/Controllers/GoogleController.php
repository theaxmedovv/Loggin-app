<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        // Redirect the user to Google's OAuth 2.0 server
        $googleAuthUrl = 'https://accounts.google.com/o/oauth2/auth?client_id=' . config('services.google.client_id') . '&redirect_uri=' . urlencode(config('services.google.redirect')) . '&response_type=code&scope=email%20profile';
        return redirect($googleAuthUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        $code = $request->input('code');

        if (!$code) {
            return redirect('/login')->withErrors('Authorization code not received.');
        }

        // Exchange authorization code for access token
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.google.redirect'),
        ]);

        if ($response->failed()) {
            return redirect('/login')->withErrors('Failed to obtain access token.');
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];

        // Get user info from Google
        $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');

        if ($userResponse->failed()) {
            return redirect('/login')->withErrors('Failed to get user information.');
        }

        $googleUser = $userResponse->json();

        // Find or create user
        $user = User::where('google_id', $googleUser['id'])->first();

        if (!$user) {
            $user = User::where('email', $googleUser['email'])->first();

            if ($user) {
                // Update existing user with Google ID
                $user->update(['google_id' => $googleUser['id']]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'google_id' => $googleUser['id'],
                    'password' => bcrypt(Str::random(16)), // Random password for Google users
                ]);
            }
        }

        // Log the user in
        Auth::login($user);

        LoginHistory::create([
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'created_at' => now(),
        ]);

        return redirect('/dashboard');
    }


}

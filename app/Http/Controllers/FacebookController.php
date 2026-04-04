<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FacebookController extends Controller
{
    public function redirectToFacebook()
    {
        $query = http_build_query([
            'client_id' => config('services.facebook.client_id'),
            'redirect_uri' => config('services.facebook.redirect'),
            'state' => csrf_token(),
            'scope' => 'email',
            'response_type' => 'code',
        ]);

        return redirect('https://www.facebook.com/v19.0/dialog/oauth?' . $query);
    }

    public function handleFacebookCallback(Request $request)
    {
        $code = $request->input('code');

        if (! $code) {
            return redirect('/login')->withErrors('Facebook authorization failed.');
        }

        $response = Http::asForm()->post('https://graph.facebook.com/v19.0/oauth/access_token', [
            'client_id' => config('services.facebook.client_id'),
            'redirect_uri' => config('services.facebook.redirect'),
            'client_secret' => config('services.facebook.client_secret'),
            'code' => $code,
        ]);

        if ($response->failed()) {
            return redirect('/login')->withErrors('Failed to get Facebook access token.');
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'] ?? null;

        if (! $accessToken) {
            return redirect('/login')->withErrors('Facebook access token missing.');
        }

        $userResponse = Http::get('https://graph.facebook.com/me', [
            'fields' => 'id,name,email',
            'access_token' => $accessToken,
        ]);

        if ($userResponse->failed()) {
            return redirect('/login')->withErrors('Failed to get Facebook user information.');
        }

        $facebookUser = $userResponse->json();

        if (empty($facebookUser['email'])) {
            return redirect('/login')->withErrors('Facebook did not return an email address.');
        }

        $user = User::where('facebook_id', $facebookUser['id'])->first();

        if (! $user) {
            $user = User::where('email', $facebookUser['email'])->first();

            if ($user) {
                $user->update(['facebook_id' => $facebookUser['id']]);
            } else {
                $user = User::create([
                    'name' => $facebookUser['name'] ?? 'Facebook User',
                    'email' => $facebookUser['email'],
                    'facebook_id' => $facebookUser['id'],
                    'password' => bcrypt(Str::random(16)),
                ]);
            }
        }

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

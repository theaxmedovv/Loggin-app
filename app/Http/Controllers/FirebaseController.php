<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class FirebaseController extends Controller
{
    public function showPhoneForm()
    {
        // Clear any existing OTP session data when showing the form
        // This allows users to change their phone number
        Session::forget(['phone_number', 'otp_code', 'otp_sent']);

        return view('auth.phone');
    }

    public function sendOTP(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^\+[1-9]\d{1,14}$/',
        ]);

        $phoneNumber = $request->input('phone_number');

        try {
            // For server-side phone auth, we'll use a simplified approach
            // In production, you should use Firebase Admin SDK or a proper SMS service

            // Store phone number in session
            Session::put('phone_number', $phoneNumber);
            Session::put('otp_code', '123456'); // For testing - replace with real SMS service
            Session::put('otp_sent', true);

            return redirect()->route('firebase.phone')->with('success', 'OTP sent to your phone number. For testing, use: 123456');

        } catch (\Exception $e) {
            return back()->withErrors(['phone_number' => 'Error sending OTP: ' . $e->getMessage()]);
        }
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $otp = $request->input('otp');
        $phoneNumber = Session::get('phone_number');
        $storedOtp = Session::get('otp_code');

        if (!$phoneNumber) {
            // Clear session and redirect to start fresh
            Session::forget(['phone_number', 'otp_code', 'otp_sent']);
            return redirect()->route('firebase.phone')->withErrors(['general' => 'Session expired. Please enter your phone number again.']);
        }

        if ($otp !== $storedOtp) {
            // Don't clear session on wrong OTP, just show error
            return back()->withErrors(['otp' => 'Invalid OTP. Please check the code and try again.']);
        }

        try {
            // Find or create user
            $user = User::where('phone_number', $phoneNumber)->first();

            if (!$user) {
                $user = User::create([
                    'name' => 'Phone User ' . substr($phoneNumber, -4),
                    'phone_number' => $phoneNumber,
                    'password' => bcrypt(Str::random(16)),
                ]);
            }

            // Log the user in
            Auth::login($user);

            // Record login history
            LoginHistory::create([
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'created_at' => now(),
            ]);

            // Clear session after successful login
            Session::forget(['phone_number', 'otp_code', 'otp_sent']);

            return redirect('/dashboard');

        } catch (\Exception $e) {
            // Clear session on error to prevent stuck state
            Session::forget(['phone_number', 'otp_code', 'otp_sent']);
            return redirect()->route('firebase.phone')->withErrors(['general' => 'An error occurred. Please try again.']);
        }
    }

    public function handleCallback(Request $request)
    {
        $idToken = $request->input('id_token');

        if (!$idToken) {
            return redirect('/login')->withErrors('Firebase ID token not provided.');
        }

        try {
            // Verify Firebase ID token using Google's API
            $response = Http::post('https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . config('services.firebase.api_key'), [
                'idToken' => $idToken,
            ]);

            if ($response->failed()) {
                return redirect('/login')->withErrors('Invalid Firebase token.');
            }

            $firebaseUser = $response->json()['users'][0];

            // Extract user information
            $phoneNumber = $firebaseUser['phoneNumber'] ?? null;
            $name = $firebaseUser['displayName'] ?? 'Firebase User';

            if (!$phoneNumber) {
                return redirect('/login')->withErrors('Phone number not found in Firebase user data.');
            }

            // Find or create user
            $user = User::where('phone_number', $phoneNumber)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'phone_number' => $phoneNumber,
                    'password' => bcrypt(Str::random(16)),
                ]);
            }

            // Log the user in
            Auth::login($user);

            // Record login history
            LoginHistory::create([
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'created_at' => now(),
            ]);

            return redirect('/dashboard');

        } catch (\Exception $e) {
            return redirect('/login')->withErrors('Firebase authentication failed: ' . $e->getMessage());
        }
    }
}

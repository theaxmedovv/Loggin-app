# Firebase Phone Authentication Setup

## 1. Firebase Console Setup

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project or select existing one
3. Enable Authentication:
    - Go to Authentication > Sign-in method
    - Enable "Phone" sign-in method

## 2. Get Firebase Configuration

1. Go to Project Settings (gear icon)
2. Scroll to "Your apps" section
3. Click "Add app" > Web app
4. Copy the config values

## 3. Update Environment Variables

Update your `.env` file with Firebase config:

```env
# Firebase Configuration
VITE_FIREBASE_API_KEY=your_api_key_here
VITE_FIREBASE_AUTH_DOMAIN=your_project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your_project_id
VITE_FIREBASE_STORAGE_BUCKET=your_project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=your_sender_id
VITE_FIREBASE_APP_ID=your_app_id
```

## 4. Test Phone Authentication

1. Start your Laravel server: `php artisan serve`
2. Visit login page
3. Click "Phone" button
4. Enter phone number (with country code, e.g., +1234567890)
5. Enter the OTP received via SMS
6. User will be logged in and redirected to dashboard

## Features

- Phone number authentication with SMS OTP
- Automatic user creation for new phone numbers
- Login history tracking
- Integration with existing Laravel authentication system

## Security Notes

- Firebase handles reCAPTCHA verification automatically
- Phone authentication is secure and doesn't require passwords
- All authentication data is handled client-side with Firebase SDK

<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-4">
                <h2 class="text-2xl font-bold text-center text-gray-800">Phone Authentication</h2>
                <p class="text-center text-gray-600 mt-2">Enter your phone number to receive an OTP</p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- General Errors -->
            @if($errors->has('general'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $errors->first('general') }}
                </div>
            @endif

            @if(!session('otp_sent'))
                <!-- Phone Number Form -->
                <form method="POST" action="{{ route('firebase.send-otp') }}">
                    @csrf

                    <!-- Phone Number -->
                    <div>
                        <x-input-label for="phone_number" :value="__('Phone Number')" />
                        <x-text-input id="phone_number" class="block mt-1 w-full" type="tel" name="phone_number"
                                      :value="old('phone_number')" required autofocus
                                      placeholder="+1234567890" />
                        <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                        <p class="text-xs text-gray-500 mt-1">Enter your phone number with country code (e.g., +998901234567)</p>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4" href="{{ route('login') }}">
                            {{ __('Back to Login') }}
                        </a>

                        <x-primary-button>
                            {{ __('Send OTP') }}
                        </x-primary-button>
                    </div>
                </form>
            @else
                <!-- OTP Verification Form -->
                <form method="POST" action="{{ route('firebase.verify-otp') }}">
                    @csrf

                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            OTP sent to: <strong>{{ session('phone_number') }}</strong>
                        </p>
                    </div>

                    <!-- OTP -->
                    <div>
                        <x-input-label for="otp" :value="__('Enter OTP')" />
                        <x-text-input id="otp" class="block mt-1 w-full" type="text" name="otp"
                                      :value="old('otp')" required autofocus
                                      placeholder="123456" maxlength="6" pattern="[0-9]{6}"
                                      title="Please enter a 6-digit OTP code" />
                        <x-input-error :messages="$errors->get('otp')" class="mt-2" />
                        <p class="text-xs text-gray-500 mt-1">Enter the 6-digit code sent to your phone</p>
                    </div>

                    <script>
                        // Focus on OTP input when the page loads with OTP form
                        document.addEventListener('DOMContentLoaded', function() {
                            const otpInput = document.getElementById('otp');
                            if (otpInput) {
                                otpInput.focus();
                                // Also select all text if there's a value
                                if (otpInput.value) {
                                    otpInput.select();
                                }
                            }
                        });

                        // Prevent accidental form submission when editing OTP
                        document.addEventListener('DOMContentLoaded', function() {
                            const otpForm = document.querySelector('form[action*="verify-otp"]');
                            const otpInput = document.getElementById('otp');

                            if (otpForm && otpInput) {
                                // Only allow form submission when OTP has content and is the right length
                                otpForm.addEventListener('submit', function(e) {
                                    const otpValue = otpInput.value.trim();
                                    if (otpValue.length !== 6) {
                                        e.preventDefault();
                                        alert('Please enter a 6-digit OTP code.');
                                        otpInput.focus();
                                        return false;
                                    }
                                });

                                // Prevent form submission on Enter if OTP is incomplete
                                otpInput.addEventListener('keydown', function(e) {
                                    if (e.key === 'Enter') {
                                        const otpValue = otpInput.value.trim();
                                        if (otpValue.length !== 6) {
                                            e.preventDefault();
                                            alert('Please enter a complete 6-digit OTP code before submitting.');
                                            return false;
                                        }
                                    }
                                });
                            }
                        });
                    </script>

                    <div class="flex items-center justify-between mt-4">
                        <a href="{{ route('firebase.phone') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                            Change Phone Number
                        </a>

                        <div class="flex space-x-2">
                            <a href="{{ route('firebase.phone') }}"
                               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 inline-block text-center">
                                Back
                            </a>

                            <x-primary-button>
                                {{ __('Verify OTP') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-guest-layout>

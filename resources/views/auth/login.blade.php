<x-guest-layout>
  <!-- Session Status -->
  <x-auth-session-status class="mb-6" :status="session('status')" />

  <form method="POST" action="{{ route('login') }}" class="w-full">
    @csrf

    <!-- Email Address -->
    <div class="mb-6">
      <x-input-label for="login" :value="__('Email or Username')" class="block text-sm font-medium text-gray-700 mb-2" />
      <x-text-input id="login"
        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
        type="text" name="login" :value="old('login')" required autofocus autocomplete="username"
        placeholder="{{ __('Enter your email or username') }}" />
      <x-input-error :messages="$errors->get('login')" class="mt-2 text-sm" />
    </div>

    <!-- Password -->
    <div class="mb-6">
      <x-input-label for="password" :value="__('Password')" class="block text-sm font-medium text-gray-700 mb-2" />

      <div class="relative">
        <x-text-input id="password"
          class="block w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
          type="password" name="password" required autocomplete="current-password"
          placeholder="{{ __('Enter your password') }}" />

        <!-- Toggle Button -->
        <button type="button" onclick="togglePasswordVisibility()"
          class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-indigo-600 focus:outline-none">

          <!-- Icon Mata -->
          <svg id="eye-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path id="eye-path" stroke-linecap="round" stroke-linejoin="round"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
        </button>
      </div>

      <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm" />
    </div>

    <!-- Remember Me & Forgot Password -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
      <label for="remember_me" class="inline-flex items-center cursor-pointer">
        <input id="remember_me" type="checkbox"
          class="w-4 h-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
        <span class="ms-2 text-sm text-gray-600 select-none">{{ __('Remember me') }}</span>
      </label>

      @if (Route::has('password.request'))
        <a class="text-sm text-indigo-600 hover:text-indigo-700 font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200"
          href="{{ route('password.request') }}">
          {{ __('Forgot password?') }}
        </a>
      @endif
    </div>

    <!-- Login Button -->
    <div class="w-full">
      <x-primary-button class="w-full py-3 px-4 text-base font-medium">
        {{ __('Log in') }}
      </x-primary-button>
    </div>
  </form>
  <script>
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const eyePath = document.getElementById('eye-path');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        // Mengubah SVG menjadi icon mata coret (Hide)
        eyePath.setAttribute('d',
          'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 014.132-5.4M9.9 4.243A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.4M9.9 4.243a3.5 3.5 0 00-4.122 4.122M19.5 19.5l-15-15'
          );
      } else {
        passwordInput.type = 'password';
        // Mengubah kembali ke icon mata biasa (Show)
        eyePath.setAttribute('d',
          'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'
          );
      }
    }
  </script>
</x-guest-layout>

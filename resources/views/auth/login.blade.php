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
        type="text" 
        name="login" 
        :value="old('login')" 
        required
        autofocus 
        autocomplete="username"
        placeholder="{{ __('Enter your email or username') }}" />
      <x-input-error :messages="$errors->get('login')" class="mt-2 text-sm" />
    </div>

    <!-- Password -->
    <div class="mb-6">
      <x-input-label for="password" :value="__('Password')" class="block text-sm font-medium text-gray-700 mb-2" />
      <x-text-input id="password" 
        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" 
        type="password" 
        name="password" 
        required
        autocomplete="current-password"
        placeholder="{{ __('Enter your password') }}" />
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
</x-guest-layout>

<x-guest-layout>
    <x-auth-session-status class="auth-session-status" :status="session('status')" />

    <form class="auth-form" method="POST" action="{{ route('login') }}">
        @csrf

        <div class="auth-field">
            <label class="auth-label" for="email">Email address</label>
            <div class="auth-input-wrap">
                <svg class="auth-field-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 6.75h16v10.5a1.75 1.75 0 0 1-1.75 1.75H5.75A1.75 1.75 0 0 1 4 17.25V6.75Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                    <path d="m4.5 7.5 7.5 5.75 7.5-5.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="auth-input-error" />
        </div>

        <div class="auth-field">
            <div class="auth-label-row">
                <label class="auth-label" for="password">Password</label>
                @if (Route::has('password.request'))
                    <a class="auth-text-link" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            <div class="auth-input-wrap">
                <svg class="auth-field-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6" />
                    <path d="M8 10V7.5a4 4 0 0 1 8 0V10m-4 4v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
                <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
            </div>
            <x-input-error :messages="$errors->get('password')" class="auth-input-error" />
        </div>

        <label class="auth-remember" for="remember_me">
            <input id="remember_me" type="checkbox" name="remember">
            <span>Remember me</span>
        </label>

        <button class="auth-submit" type="submit">Sign in <span aria-hidden="true">&#8594;</span></button>
    </form>
</x-guest-layout>

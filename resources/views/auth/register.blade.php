<x-guest-layout>
    <form class="auth-form" method="POST" action="{{ route('register') }}">
        @csrf

        <div class="auth-field">
            <label class="auth-label" for="name">Full name</label>
            <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Your name">
            <x-input-error :messages="$errors->get('name')" class="auth-input-error" />
        </div>

        <div class="auth-field">
            <label class="auth-label" for="email">Email address</label>
            <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@example.com">
            <x-input-error :messages="$errors->get('email')" class="auth-input-error" />
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">Password</label>
            <input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" placeholder="Create a password">
            <x-input-error :messages="$errors->get('password')" class="auth-input-error" />
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Enter your password again">
            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-input-error" />
        </div>

        <button class="auth-submit" type="submit">Create account <span aria-hidden="true">→</span></button>
    </form>
</x-guest-layout>
